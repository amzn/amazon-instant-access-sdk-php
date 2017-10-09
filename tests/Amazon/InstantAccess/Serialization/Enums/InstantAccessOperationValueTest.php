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
namespace Amazon\InstantAccess\Serialization\Enums;

class InstantAccessOperationValueTest extends \PHPUnit_Framework_TestCase
{
    public function testValidValues()
    {
        $this->assertTrue(InstantAccessOperationValue::isValid(InstantAccessOperationValue::PURCHASE));
        $this->assertTrue(InstantAccessOperationValue::isValid(InstantAccessOperationValue::REVOKE));
        $this->assertTrue(InstantAccessOperationValue::isValid(InstantAccessOperationValue::GET_USER_ID));
        $this->assertTrue(InstantAccessOperationValue::isValid(InstantAccessOperationValue::SUBSCRIPTION_ACTIVATE));
        $this->assertTrue(InstantAccessOperationValue::isValid(InstantAccessOperationValue::SUBSCRIPTION_DEACTIVATE));
    }

    public function testInvalidValues()
    {
        $this->assertFalse(InstantAccessOperationValue::isValid("test"));
        $this->assertFalse(InstantAccessOperationValue::isValid(""));
        $this->assertFalse(InstantAccessOperationValue::isValid(null));
        $this->assertFalse(InstantAccessOperationValue::isValid(array()));
    }
}
