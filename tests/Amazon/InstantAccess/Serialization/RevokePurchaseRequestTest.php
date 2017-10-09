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

use Amazon\InstantAccess\Serialization\Enums\RevokePurchaseReasonValue;
use Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue;

class RevokePurchaseRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromJson()
    {
        $json = '{
                    "operation":     "Revoke",
                    "reason":        "CUSTOMER_SERVICE_REQUEST",
                    "productId":     "GamePack1",
                    "userId":        "123456",
                    "purchaseToken": "6f3092e5-0326-42b7-a107-416234d548d8"
                }';

        $revokeRequest = RevokePurchaseRequest::createFromJson($json);

        $this->assertNotNull($revokeRequest);
        $this->assertEquals(InstantAccessOperationValue::REVOKE, $revokeRequest->getOperation());
        $this->assertEquals(RevokePurchaseReasonValue::CUSTOMER_SERVICE_REQUEST, $revokeRequest->getReason());
        $this->assertEquals('GamePack1', $revokeRequest->getProductId());
        $this->assertEquals('123456', $revokeRequest->getUserId());
        $this->assertEquals('6f3092e5-0326-42b7-a107-416234d548d8', $revokeRequest->getPurchaseToken());
    }

    public function testCreateFromJsonInvalidReason()
    {
        $this->setExpectedException('InvalidArgumentException');

        $json = '{
                    "operation":     "Revoke",
                    "reason":        "USER_MISCLICK",
                    "productId":     "GamePack1",
                    "userId":        "123456",
                    "purchaseToken": "6f3092e5-0326-42b7-a107-416234d548d8"
                }';

        $revokeRequest = RevokePurchaseRequest::createFromJson($json);
    }
}
