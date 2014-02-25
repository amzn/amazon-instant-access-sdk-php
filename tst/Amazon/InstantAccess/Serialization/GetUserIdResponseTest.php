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

use Amazon\InstantAccess\Serialization\Enums\GetUserIdResponseValue;

class GetUserIdResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testToJson()
    {
        $getUserIdResponse = new GetUserIdResponse();
        $getUserIdResponse->setResponse(GetUserIdResponseValue::OK);
        $getUserIdResponse->setUserId('1234');

        $json = $getUserIdResponse->toJson();

        $this->assertNotEmpty($getUserIdResponse);
        $this->assertEquals('{"userId":"1234","response":"OK"}', $json);
    }

    public function testToJsonInvalidResponse()
    {
        $this->setExpectedException('InvalidArgumentException');

        $getUserIdResponse = new GetUserIdResponse();
        $getUserIdResponse->setResponse('foobar');

        $json = $getUserIdResponse->toJson();
    }
}
