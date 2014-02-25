<?php
/**
 * Copyright 2014 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License").
 * You may not use this file except in compliance with the License.
 * A copy of the License is located at
 *
 *  http://aws.amazon.com/apache2.0
 *
 * or in the "license" file accompanying this file. This file is distributed
 * on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * permissions and limitations under the License.
 */
namespace Amazon\InstantAccess\Signature;

use Amazon\InstantAccess\Log\Logger;
use Amazon\InstantAccess\Signature\AuthorizationHeader;
use Amazon\InstantAccess\Signature\Credential;
use Amazon\InstantAccess\Signature\CredentialStore;
use Amazon\InstantAccess\Signature\Request;
use Amazon\InstantAccess\Utils\DateUtils;
use Amazon\InstantAccess\Utils\HttpUtils;

/**
 * @internal
 *
 * This class contains the logic to sign and verify a request.
 *
 * The signature algorithm is based off of AwsAuthV4
 * (http://docs.aws.amazon.com/general/latest/gr/signature-version-4.html).
 *
 * However it has minor changes to support two way communication between Amazon and External Parties.
 */
class Signer
{
    // Specified by the AWSAuthv4 to be a 15 minute window.
    const TIME_TOLERANCE_IN_MS = 900000; //15 * 1000 * 60;
    const ALGORITHM_ID = 'DTA1-HMAC-SHA256';

    /**
     * Signs the request and adds the authentication headers (Authentication & x-amz-date).
     *
     * @param Request $request the request to sign.
     * @param Credential $credential the credential to use when signing.
     */
    public function sign(Request $request, Credential $credential)
    {
        $dateNow = new \DateTime('@' . time());

        $shortDate = $dateNow->format(DateUtils::DATE_FORMAT_SHORT);
        $isoDate = $dateNow->format(DateUtils::DATE_FORMAT_ISO8601);

        $headers = &$request->getHeaders();

        $headers[HttpUtils::X_AMZ_DATE_HEADER] = $isoDate;

        // Remove the Authorization header from the request, since it could have been set if sign() was previously
        // called on this request.
        unset($headers[HttpUtils::AUTHORIZATION_HEADER]);

        $authorizationHeader = $this->getAuthorizationHeader($request, $credential, $shortDate, $isoDate);

        Logger::getLogger()->debug(sprintf('Signing request with header: %s', $authorizationHeader));

        $headers[HttpUtils::AUTHORIZATION_HEADER] = $authorizationHeader;
    }

    /**
     * Verifies the request signature against a credential store.
     *
     * @param Request $request the request to verify.
     * @param CredentialStore $credentialStore the credential store used to verify the request.
     *
     * @return boolean returns true if the request validates.
     */
    public function verify(Request $request, CredentialStore $credentialStore)
    {
        if (count($credentialStore->getAll()) == 0) {
            throw new \InvalidArgumentException('Empty credential store.');
        }

        $dateNow = new \DateTime('@' . time());

        $shortDate = $dateNow->format(DateUtils::DATE_FORMAT_SHORT);

        $headers = $request->getHeaders();

        if (!isset($headers[HttpUtils::X_AMZ_DATE_HEADER])) {
            Logger::getLogger()->warning(
                sprintf('Header %s not found, aborting request verification.', HttpUtils::X_AMZ_DATE_HEADER)
            );
            return false;
        }
        $requestIsoDate = $headers[HttpUtils::X_AMZ_DATE_HEADER];

        if (!isset($headers[HttpUtils::AUTHORIZATION_HEADER])) {
            Logger::getLogger()->warning(
                sprintf('Header %s not found, aborting request verification.', HttpUtils::AUTHORIZATION_HEADER)
            );
            return false;
        }
        $actualAuthorization = $headers[HttpUtils::AUTHORIZATION_HEADER];

        $authorizationHeader = null;
        try {
            $authorizationHeader = AuthorizationHeader::parse($actualAuthorization);
        } catch (\InvalidArgumentException $e) {
            Logger::getLogger()->warning(
                sprintf('Unable to parse Authorization header, aborting request verification.')
            );
            return false;
        }

        $signedHeaders = $authorizationHeader->getSignedHeaders();

        // remove unsigned headers
        $unsignedHeaders = array_diff_key(($request->getHeaders()), array_flip($signedHeaders));
        $request->filterHeaders(array_keys($unsignedHeaders));

        $dateOfRequest = \DateTime::createFromFormat(DateUtils::DATE_FORMAT_ISO8601, $requestIsoDate);
        $delta = $dateOfRequest->getTimestamp() - $dateNow->getTimestamp();
        if (abs($delta) > self::TIME_TOLERANCE_IN_MS) {
            Logger::getLogger()->warning(sprintf('Time tolerance exceeded, aborting request verification.'));
            return false;
        }

        $credentialInfo = $authorizationHeader->getCredential();

        $credential = $credentialStore->get($credentialInfo['key']);
        if (!$credential) {
            Logger::getLogger()->warning(
                sprintf('Public key not found: %s, aborting request verification.', $credentialInfo['key'])
            );
            return false;
        }

        if ($dateOfRequest->format(DateUtils::DATE_FORMAT_SHORT) != $credentialInfo['date']) {
            Logger::getLogger()->warning(
                sprintf('Request date and credential date don`t match aborting request verification.')
            );
            return false;
        }

        $authorizationHeader = $this->getAuthorizationHeader($request, $credential, $shortDate, $requestIsoDate);

        Logger::getLogger()->debug(
            sprintf(
                'Verifying request with header: %s, against expected header: %s',
                $actualAuthorization,
                $authorizationHeader
            )
        );

        if ($authorizationHeader == $actualAuthorization) {
            return true;
        } else {
            Logger::getLogger()->warning(sprintf('Authorization signature doesn`t match, verification failed.'));
            return false;
        }
    }

    /**
     * Returns the autorization header based on the parameters
     *
     * @param Request $request the request to generate the signature from
     * @param Credential $credential the credential to use when signing
     * @param string $shortDate the date to use to sign in short format
     * @param string $isoDate the date to use to sign in iso format
     *
     * @return string a string representation of the authorization header
     */
    public function getAuthorizationHeader(Request $request, Credential $credential, $shortDate, $isoDate)
    {
        if (!$shortDate || !$isoDate) {
            throw new \InvalidArgumentException('Invalid dates.');
        }

        $timedKey = hash_hmac('sha256', $shortDate, $credential->getSecretKey(), true);

        $canonicalRequest = $this->getCanonicalRequest($request);

        // We don't use scope in this algorithm
        $scope = '';

        $stringToSign = self::ALGORITHM_ID . "\n" . $isoDate . "\n" . $scope . "\n" . hash('sha256', $canonicalRequest);

        Logger::getLogger()->debug(sprintf('String to sign: %s', $stringToSign));

        $signature = hash_hmac('sha256', $stringToSign, $timedKey);

        $headers = $request->getHeaders();
        ksort($headers);

        $authorizationHeader = new AuthorizationHeader(
            self::ALGORITHM_ID,
            array_keys($headers),
            array('key' => $credential->getPublicKey(), 'date' => $shortDate),
            $signature
        );

        return $authorizationHeader->__tostring();
    }

    /**
     * Returns the canonical representation of the request. The canonical request is of the form:
     *
     * <pre>
     * METHOD
     * CANONICAL_PATH
     * CANONICAL_QUERY_STRING
     * CANONICAL_HEADER_STRING
     * SIGNED_HEADERS
     * CONTENT_HASH
     * </pre>
     *
     * Which for a get request to http://amazon.com/ would be:
     *
     * <pre>
     * GET
     * /
     *
     * x-amz-date:20110909T233600Z
     * 230d8358dc8e8890b4c58deeb62912ee2f20357ae92a5cc861b98e68fe31acb5
     * </pre>
     *
     * @param Request $request the request to canonicalize.
     * @return string the canonical request.
     */
    private function getCanonicalRequest(Request $request)
    {
        // Method
        $canonicalRequest  = $request->getMethod() . "\n";

        // Path
        $url = parse_url($request->getUrl());
        $canonicalRequest .= HttpUtils::normalizePath($url['path']) . "\n";

        // Query string
        // TODO: the Java SDK does not add the query string to the canonical form
        $canonicalRequest .= "\n";

        // Headers
        $headers = array();
        foreach ($request->getHeaders() as $key => $value) {
            $headers[$key] = preg_replace('/\s+/', ' ', trim($value));
        }

        ksort($headers);

        foreach ($headers as $key => $value) {
            $canonicalRequest .= $key . ':' . $value . "\n";
        }

        // TODO: the Java SDK adds a empty line after the headers
        $canonicalRequest .= "\n";

        // Signed headers
        $headers = $request->getHeaders();
        ksort($headers);

        $canonicalRequest .= implode(';', array_keys($headers)) . "\n";

        // Content hash
        $canonicalRequest .= hash('sha256', $request->getBody());

        Logger::getLogger()->debug(sprintf('Canonical request: %s', $canonicalRequest));

        return $canonicalRequest;
    }
}
