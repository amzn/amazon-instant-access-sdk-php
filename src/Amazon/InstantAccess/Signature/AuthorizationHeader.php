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

/**
 * @internal
 *
 * Class representing the authorization header, which follows the pattern:
 *
 * <pre>
 * Authorization: ALGORITHM Credential=CREDENTIAL, SignedHeaders=SIGNED_HEADERS, Signature=SIGNATURE
 * </pre>
 *
 * Where:
 * ALGORITHM := The signing algorithm used for the credential, ex. DTAv1-SHA-256
 * CREDENTIAL := KEYID/DATE.
 * SIGNED_HEADERS := lower cased header names sorted by byte order joined with semicolons.
 * SIGNATURE := The signature calculated by the signing algorithm.
 * KEYID := The public id for the sceret key used to calculate the signature.
 * DATE := The date the message was signed in YYMMDD format. This is used to generate the daily key.
 */
class AuthorizationHeader
{
    /** @var string */
    private $algorithm;
    /** @var string */
    private $credential;
    /** @var string */
    private $signedHeaders;
    /** @var string */
    private $signature;

    const AUTHORIZATION_HEADER_PATTERN = '/(\S+) SignedHeaders=(\S+), Credential=(\S+), Signature=([\S]+)$/';

    /**
     * Parses header value string an returns a new object.
     *
     * @param string $headerString the string representation of the Authentication header
     *
     * @return AuthorizationHeader a new {@link AuthorizationHeader} object if header is valid
     *
     * @throws InvalidArgumentException if unable to parse header
     */
    public static function parse($headerString)
    {
        if (empty($headerString)) {
            throw new \InvalidArgumentException('Invalid authorization header.');
        }

        preg_match(self::AUTHORIZATION_HEADER_PATTERN, $headerString, $matches);

        if (!$matches) {
            throw new \InvalidArgumentException('Unable to parse authorization header.');
        }

        // split headers by ';' and convert them to lower case
        $signedHeaders = array_map('strtolower', explode(';', $matches[2]));

        // the credential should follow this pattern: PUBLIC_KEY_ID/DATE
        $credential = explode('/', $matches[3]);

        if (count($credential) < 2) {
            throw new \InvalidArgumentException('Invalid credential format.');
        }

        $credential = array('key' => $credential[0],
                            'date' => $credential[1]);

        // the algorithm used to generate the signature
        $algorithm = $matches[1];

        // the signature of the request
        $signature = $matches[4];

        $header = new AuthorizationHeader(
            $algorithm,
            $signedHeaders,
            $credential,
            $signature
        );

        return $header;
    }

    public function __construct($algorithm, $signedHeaders, $credential, $signature)
    {
        if (empty($algorithm)) {
            throw new \InvalidArgumentException('Empty algorithm information.');
        }

        if (!is_array($signedHeaders)) {
            throw new \InvalidArgumentException('Invalid signed headers array.');
        }

        if (!is_array($credential) || !array_key_exists('key', $credential) || !array_key_exists('date', $credential)) {
            throw new \InvalidArgumentException('Invalid credential information.');
        }

        if (empty($signature)) {
            throw new \InvalidArgumentException('Empty signature information.');
        }

        $this->algorithm = $algorithm;
        $this->signedHeaders = $signedHeaders;
        $this->credential = $credential;
        $this->signature = $signature;
    }

    public function getAlgorithm()
    {
        return $this->algorithm;
    }

    public function getCredential()
    {
        return $this->credential;
    }

    public function getSignedHeaders()
    {
        return $this->signedHeaders;
    }

    public function getSignature()
    {
        return $this->signature;
    }

    public function __tostring()
    {
        $str = $this->algorithm . ' ';
        $str .= 'SignedHeaders=' . implode(';', $this->signedHeaders) . ', ';
        $str .= 'Credential=' . $this->credential['key'] . '/'. $this->credential['date'] .', ';
        $str .= 'Signature=' . $this->signature;

        return $str;
    }
}
