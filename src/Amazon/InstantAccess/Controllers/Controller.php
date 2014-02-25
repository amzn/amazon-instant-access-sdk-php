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
namespace Amazon\InstantAccess\Controllers;

use Amazon\InstantAccess\Log\Logger;
use Amazon\InstantAccess\Serialization\InstantAccessRequest;
use Amazon\InstantAccess\Serialization\InstantAccessResponse;
use Amazon\InstantAccess\Signature\CredentialStore;
use Amazon\InstantAccess\Signature\Request;
use Amazon\InstantAccess\Signature\Signer;
use Amazon\InstantAccess\Utils\HttpUtils;

/**
 * @internal
 *
 * This abstract class is extended by the {@link PurchaseController} and {@link AccountLinkingController} in order to
 * implement the Instant Access API. This class should not be used, use the aforementioned classes instead.
 */
abstract class Controller
{
    /** @var Request */
    protected $request;
    /** @var Signer */
    protected $signer;

    /**
     * Creates a new instant access controller.
     *
     * @param CredentialStore $credentialStore a credential store object with valid credential keys
     * @param Signer $signer a optional signer object to verify the request signature
     */
    public function __construct(CredentialStore $credentialStore, Signer $signer = null)
    {
        $this->credentialStore = $credentialStore;
        $this->signer = $signer ?: new Signer();
    }

    /**
     * Processes the specific operation and return a response.
     *
     * @param string $operation the name of the operation being processed
     *
     * @return InstantAccessResponse an object that extends {@link InstantAccessResponse} object
     */
    abstract protected function processOperation($operation);

    /**
     * Processes a request created from $_SERVER and the body of the request.
     *
     * At the end this function sets the response headers and writes the content. After calling this, be sure
     * to write the string returned to the output stream.
     *
     * NOTE: This consumes the body of the request which can cause issues when you try and read it again.
     *
     * @uses Controller::processOperation() to call the correct callback
     *
     * @param array $server the $_SERVER array
     * @param string|null $requestBody the path/stream of the body of the request, defaults to php://input
     *
     * @return string the response string ready to be sent
     */
    public function process(array $server, $requestBody = 'php://input')
    {
        try {
            Logger::getLogger()->info(sprintf('Started processing request received by %s', get_class($this)));

            // convert all errors to exception so they can be caught and not just break the script
            set_error_handler(
                function ($code, $message, $file, $line, $context) {
                    throw new \Exception($message, $code);
                }
            );

            // read content of request to a string
            $body = file_get_contents($requestBody);

            // create request object
            $this->request = new Request($server, $body);

            Logger::getLogger()->debug(sprintf('Request: %s', (string)$this->request));

            // verify the request againts the credential store
            if (!$this->signer->verify($this->request, $this->credentialStore)) {
                throw new \Exception('Request validation failed.');
            }

            // deserialize the content to a InstantAccessRequest object so we can check which operation is going
            // to be called
            $iaRequest = InstantAccessRequest::createFromJson($this->request->getBody());

            Logger::getLogger()->info(sprintf('Processing request as %s operation', $iaRequest->getOperation()));

            // process the request according to the operation
            $iaResponse = $this->processOperation($iaRequest->getOperation());

            // check if the response is valid
            if (!is_subclass_of($iaResponse, 'Amazon\InstantAccess\Serialization\InstantAccessResponse')) {
                throw new \UnexpectedValueException(
                    'Invalid response returned by the controller. ' .
                    'It needs to be a subclass of InstantAccessResponse'
                );
            }

            HttpUtils::setResponseHeader('HTTP/1.1 200 OK', true, 200);
            HttpUtils::setResponseHeader('Content-Type: application/json');

            $response = $iaResponse->toJson();

            Logger::getLogger()->debug(sprintf('Response: %s', $response));
            Logger::getLogger()->info(sprintf('Request processed succesfully'));
        } catch (\Exception $e) {
            Logger::getLogger()->error(sprintf('Unable to process request: %s', $e));
            HttpUtils::setResponseHeader('HTTP/1.1 500 Internal Server Error', true, 500);
            HttpUtils::setResponseHeader('Content-Type: text/plain');
            $response = '';
        }

        // restore previous error handler
        restore_error_handler();

        return $response;
    }
}
