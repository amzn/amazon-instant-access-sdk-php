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

use Amazon\InstantAccess\Utils\IOUtils;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateRequest()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/index.html?a=1';
        $server['REQUEST_METHOD'] = 'POST';
        $server['HTTP_ACCEPT'] = '*/*';

        $content = '{}';

        $request = new Request($server, $content);

        $this->assertEquals('http://amazon.com/index.html?a=1', $request->getUrl());
        $this->assertEquals('POST', $request->getMethod());
        $this->assertEquals('{}', $request->getBody());

        $this->assertEquals(array('host' => 'amazon.com', 'accept' => '*/*'), $request->getHeaders());
    }

    public function testFilterHeaders()
    {
        $server = array();
        $server['HTTP_HOST'] = 'amazon.com';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['REQUEST_URI'] = '/index.html?a=1';
        $server['REQUEST_METHOD'] = 'POST';
        $server['HTTP_ACCEPT'] = '*/*';

        $request = new Request($server);

        $filter = array('host');

        $request->filterHeaders($filter);

        $this->assertEquals(1, count($request->getHeaders()));
        $this->assertEquals(array('accept' => '*/*'), $request->getHeaders());
    }
}
