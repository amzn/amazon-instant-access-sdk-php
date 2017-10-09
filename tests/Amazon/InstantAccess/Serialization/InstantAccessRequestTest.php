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

class InstantAccessRequestTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateFromJson()
    {
        $json = '{
                    "operation" : "Purchase"
                 }';

        $iaRequest = InstantAccessRequest::createFromJson($json);

        $this->assertNotNull($iaRequest);
        $this->assertEquals(InstantAccessOperationValue::PURCHASE, $iaRequest->getOperation());
    }

    public function testCreateFromJsonInvalidValue()
    {
        $this->setExpectedException('InvalidArgumentException');

        $json = '{
                    "operation" : "FooBar"
                 }';

        $iaRequest = InstantAccessRequest::createFromJson($json);
    }

    public function testCreateFromJsonInvalidJson()
    {
        $this->setExpectedException('InvalidArgumentException');

        $json = '{
                    "operation" : "Purchase"
                 ';

        $iaRequest = InstantAccessRequest::createFromJson($json);
    }
}
