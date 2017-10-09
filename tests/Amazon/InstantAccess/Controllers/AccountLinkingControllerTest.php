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

use Amazon\InstantAccess\Serialization\Enums\GetUserIdResponseValue;
use Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue;
use Amazon\InstantAccess\Serialization\GetUserIdResponse;
use Amazon\InstantAccess\Signature\AuthorizationHeader;
use Amazon\InstantAccess\Signature\Credential;
use Amazon\InstantAccess\Signature\CredentialStore;
use Amazon\InstantAccess\Signature\Request;
use Amazon\InstantAccess\Signature\Signer;
use Amazon\InstantAccess\Utils\DateUtils;
use Amazon\InstantAccess\Utils\HttpUtils;
use Amazon\InstantAccess\Utils\IOUtils;

class AccountLinkingControllerTest extends \PHPUnit_Framework_TestCase
{
    private static $credential;
    private static $credentialStore;

    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        $credential = new Credential("SECRET", "PUBLIC");

        $credentialStore = new CredentialStore();
        $credentialStore->add($credential);

        self::$credential = $credential;
        self::$credentialStore = $credentialStore;
    }

    private function generateSignedRequest(&$server, $content)
    {
        $dateNow = new \DateTime('@' . time());

        $shortDate = $dateNow->format(DateUtils::DATE_FORMAT_SHORT);
        $isoDate = $dateNow->format(DateUtils::DATE_FORMAT_ISO8601);

        $server['HTTP_' . HttpUtils::X_AMZ_DATE_HEADER] = $isoDate;

        $request = new Request($server, $content);

        $signer = new Signer();
        $signer->sign($request, self::$credential);

        $headers = $request->getHeaders();
        $server['HTTP_' . HttpUtils::AUTHORIZATION_HEADER] = $headers[HttpUtils::AUTHORIZATION_HEADER];

        return $request;
    }

    public function testGetUserId()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                        "operation":  "GetUserId",
                        "infoField1": "nobody@amazon.com",
                        "infoField2": "amazon",
                        "infoField3": "nobody"
                    }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new AccountLinkingController(self::$credentialStore);

        $controller->onGetUserId(function ($req) {
            $res = new GetUserIdResponse();
            $res->setResponse(GetUserIdResponseValue::OK);
            $res->setUserId('1234');
            return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertNotNull($response);
        $this->assertEquals('{"userId":"1234","response":"OK"}', $response);
    }

    public function testGetUserIdNoCallback()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                        "operation":  "GetUserId",
                        "infoField1": "nobody@amazon.com",
                        "infoField2": "amazon",
                        "infoField3": "nobody"
                    }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new AccountLinkingController(self::$credentialStore);

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertEmpty($response);

        // TODO : assert headers for status 500
    }

    public function testProcessOperationInvalidOperation()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                        "operation":     "Purchase",
                        "reason":        "FULFILL",
                        "productId":     "GamePack1",
                        "userId":        "123456",
                        "purchaseToken": "6f3092e5-0326-42b7-a107-416234d548d8"
                    }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new AccountLinkingController(self::$credentialStore);

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertEmpty($response);

        // TODO : assert headers for status 500
    }

    public function testGetUserIdInvalidSignature()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                        "operation":  "GetUserId",
                        "infoField1": "nobody@amazon.com",
                        "infoField2": "amazon",
                        "infoField3": "nobody"
                    }';

        $request = $this->generateSignedRequest($server, $content);

        $server['HTTP_' . HttpUtils::AUTHORIZATION_HEADER] .= "foobar";

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new AccountLinkingController(self::$credentialStore);

        $controller->onGetUserId(function ($req) {
            $res = new GetUserIdResponse();
            $res->setResponse(GetUserIdResponseValue::OK);
            $res->setUserId('1234');
            return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertEmpty($response);

        // TODO : assert headers for status 500
    }

    public function testGetUserIdInvalidCallbackReturn()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                        "operation":  "GetUserId",
                        "infoField1": "nobody@amazon.com",
                        "infoField2": "amazon",
                        "infoField3": "nobody"
                    }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new AccountLinkingController(self::$credentialStore);

        $controller->onGetUserId(function ($req) {
            return "respose";
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertEmpty($response);

        // TODO : assert headers for status 500
    }
}
