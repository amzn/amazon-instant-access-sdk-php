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

class InstantAccessResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testToJson()
    {
        $iaResponse = new InstantAccessResponse();
        $iaResponse->setResponse("OK");

        $json = $iaResponse->toJson();

        $this->assertNotEmpty($iaResponse);
        $this->assertEquals('{"response":"OK"}', $json);
    }
}
