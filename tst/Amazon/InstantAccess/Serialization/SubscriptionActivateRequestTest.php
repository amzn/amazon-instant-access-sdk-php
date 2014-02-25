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
namespace Amazon\InstantAccess\Serialization;

use Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue;

class SubscriptionActivateRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromJson()
    {
        $json = '{
                     "operation":      "SubscriptionActivate",
                     "subscriptionId": "subscriptionId",
                     "productId":      "GamePack1",
                     "userId":         "1234"
                 }';

        $subRequest = SubscriptionActivateRequest::createFromJson($json);

        $this->assertNotNull($subRequest);
        $this->assertEquals(InstantAccessOperationValue::SUBSCRIPTION_ACTIVATE, $subRequest->getOperation());
        $this->assertEquals('subscriptionId', $subRequest->getSubscriptionId());
        $this->assertEquals('GamePack1', $subRequest->getProductId());
        $this->assertEquals('1234', $subRequest->getUserId());
    }
}
