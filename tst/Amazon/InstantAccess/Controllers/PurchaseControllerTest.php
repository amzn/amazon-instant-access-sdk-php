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

use Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue;
use Amazon\InstantAccess\Serialization\Enums\FulfillPurchaseResponseValue;
use Amazon\InstantAccess\Serialization\Enums\RevokePurchaseResponseValue;
use Amazon\InstantAccess\Serialization\Enums\SubscriptionActivateResponseValue;
use Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivateResponseValue;
use Amazon\InstantAccess\Serialization\FulfillPurchaseResponse;
use Amazon\InstantAccess\Serialization\RevokePurchaseResponse;
use Amazon\InstantAccess\Serialization\SubscriptionActivateResponse;
use Amazon\InstantAccess\Serialization\SubscriptionDeactivateResponse;
use Amazon\InstantAccess\Signature\Credential;
use Amazon\InstantAccess\Signature\CredentialStore;
use Amazon\InstantAccess\Signature\Request;
use Amazon\InstantAccess\Signature\Signer;
use Amazon\InstantAccess\Utils\DateUtils;
use Amazon\InstantAccess\Utils\HttpUtils;
use Amazon\InstantAccess\Utils\IOUtils;

class PurchaseControllerTest extends \PHPUnit_Framework_TestCase
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

    public function testFulfillPurchase()
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

        $controller = new PurchaseController(self::$credentialStore);

        $controller->onFulfillPurchase(function ($req) {
            $res = new FulfillPurchaseResponse();
            $res->setResponse(FulfillPurchaseResponseValue::FAIL_USER_NOT_ELIGIBLE);
            return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertNotNull($response);
        $this->assertEquals('{"response":"FAIL_USER_NOT_ELIGIBLE"}', $response);
    }

    public function testRevokePurchase()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                        "operation":     "Revoke",
                        "reason":        "CUSTOMER_SERVICE_REQUEST",
                        "productId":     "GamePack1",
                        "userId":        "123456",
                        "purchaseToken": "6f3092e5-0326-42b7-a107-416234d548d8"
                    }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new PurchaseController(self::$credentialStore);

        $controller->onRevokePurchase(function ($req) {
            $res = new RevokePurchaseResponse();
            $res->setResponse(RevokePurchaseResponseValue::FAIL_INVALID_PURCHASE_TOKEN);
            return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertNotNull($response);
        $this->assertEquals('{"response":"FAIL_INVALID_PURCHASE_TOKEN"}', $response);
    }

    public function testSubscriptionActivate()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                         "operation":      "SubscriptionActivate",
                         "subscriptionId": "subscriptionId",
                         "productId":      "GamePack1",
                         "userId":         "1234"
                     }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new PurchaseController(self::$credentialStore);

        $controller->onSubscriptionActivate(function ($req) {
            $res = new SubscriptionActivateResponse();
            $res->setResponse(SubscriptionActivateResponseValue::FAIL_USER_INVALID);
            return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertNotNull($response);
        $this->assertEquals('{"response":"FAIL_USER_INVALID"}', $response);
    }

    public function testSubscriptionActivateTeamSubsV1()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                "operation":                        "SubscriptionActivate",
                "subscriptionId":                   "subscriptionId",
                "productId":                        "GamePack1",
                "userId":                           "1234",
                "subscriptionGroupId":              "group1234",
                "numberOfSubscriptionsInGroup":     "4"
        }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new PurchaseController(self::$credentialStore);

        $controller->onSubscriptionActivate(function ($req) {
                $res = new SubscriptionActivateResponse();
                $res->setResponse(SubscriptionActivateResponseValue::FAIL_INVALID_SUBSCRIPTION);
                return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertNotNull($response);
        $this->assertEquals('{"response":"FAIL_INVALID_SUBSCRIPTION"}', $response);
    }

    public function testSubscriptionDeactivate()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/';
        $server['REQUEST_METHOD'] = 'POST';
        $server['CONTENT_TYPE'] = 'application/json';

        $content = '{
                         "operation":      "SubscriptionDeactivate",
                         "subscriptionId": "subscriptionId",
                         "reason":         "PAYMENT_PROBLEM",
                         "period":         "REGULAR"
                     }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new PurchaseController(self::$credentialStore);

        $controller->onSubscriptionDeactivate(function ($req) {
            $res = new SubscriptionDeactivateResponse();
            $res->setResponse(SubscriptionDeactivateResponseValue::OK);
            return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));

        $this->assertNotNull($response);
        $this->assertEquals('{"response":"OK"}', $response);
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
                        "operation":  "GetUserId",
                        "infoField1": "nobody@amazon.com",
                        "infoField2": "amazon",
                        "infoField3": "nobody"
                    }';

        $request = $this->generateSignedRequest($server, $content);

        $body = tmpfile();
        fwrite($body, $content);
        fseek($body, 0);

        $controller = new PurchaseController(self::$credentialStore);

        $controller->onSubscriptionDeactivate(function ($req) {
            $res = new SubscriptionDeactivateResponse();
            $res->setResponse(SubscriptionDeactivateResponseValue::OK);
            return $res;
        });

        $response = $controller->process($server, IOUtils::getFilePathFromHandle($body));
        $this->assertEmpty($response);

        // TODO : assert headers for status 500
    }
}
