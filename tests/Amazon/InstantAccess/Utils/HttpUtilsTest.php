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
namespace Amazon\InstantAccess\Utils;

class HttpUtilsTest extends \PHPUnit_Framework_TestCase
{
    public function testParseFullURL()
    {
        $server = array();

        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '80';
        $server['HTTP_HOST'] = 'amazon.com';
        $server['REQUEST_URI'] = '/index.html?a=1';

        $url = HttpUtils::parseFullURL($server);

        $this->assertNotNull($url);
        $this->assertEquals('http://amazon.com/index.html?a=1', $url);
    }

    public function testParseFullURLWithCustomPort()
    {
        $server = array();

        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '1234';
        $server['HTTP_HOST'] = 'amazon.com';
        $server['REQUEST_URI'] = '/index.html?a=1';

        $url = HttpUtils::parseFullURL($server);

        $this->assertNotNull($url);
        $this->assertEquals('http://amazon.com:1234/index.html?a=1', $url);
    }

    public function testParseFullURLWithSSL()
    {
        $server = array();

        $server['HTTPS'] = 'on';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '443';
        $server['HTTP_HOST'] = 'amazon.com';
        $server['REQUEST_URI'] = '/index.html?a=1';

        $url = HttpUtils::parseFullURL($server);

        $this->assertNotNull($url);
        $this->assertEquals('https://amazon.com/index.html?a=1', $url);
    }

    public function testParseFullURLWithSSLAndCustomPort()
    {
        $server = array();

        $server['HTTPS'] = 'on';
        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '1234';
        $server['HTTP_HOST'] = 'amazon.com';
        $server['REQUEST_URI'] = '/index.html?a=1';

        $url = HttpUtils::parseFullURL($server);

        $this->assertNotNull($url);
        $this->assertEquals('https://amazon.com:1234/index.html?a=1', $url);
    }

    public function testParseFullURLWithForwardedHost()
    {
        $server = array();

        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '1234';
        $server['HTTP_HOST'] = 'amazon.com';
        $server['HTTP_X_FORWARDED_HOST'] = 'amazon2.com';
        $server['REQUEST_URI'] = '/index.html?a=1';

        $url = HttpUtils::parseFullURL($server);

        $this->assertNotNull($url);
        $this->assertEquals('http://amazon2.com:1234/index.html?a=1', $url);
    }

    public function testParseFullURLWithServerName()
    {
        $server = array();

        $server['SERVER_PROTOCOL'] = 'HTTP/1.1';
        $server['SERVER_PORT'] = '1234';
        $server['SERVER_NAME'] = 'amazon.com';
        $server['REQUEST_URI'] = '/index.html?a=1';

        $url = HttpUtils::parseFullURL($server);

        $this->assertNotNull($url);
        $this->assertEquals('http://amazon.com:1234/index.html?a=1', $url);
    }

    public function testParseRequestHeaders()
    {
        $server = array();

        $server['HTTP_HOST'] = 'amazon.com';
        $server['HTTP_ACCEPT'] = 'text/html,application/json;q=1';
        $server['HTTP_CONNECTION'] = 'keep-alive';
        $server['HTTP_ACCEPT_ENCODING'] = 'gzip,deflate,sdch';
        $server['SERVER_PORT'] = '80';

        $server['CONTENT_TYPE'] = 'application/json';
        $server['CONTENT_LENGTH'] = '32';

        $headers = HttpUtils::parseRequestHeaders($server);

        $this->assertNotNull($headers);
        $this->assertEquals(6, count($headers));
        $this->assertEquals('amazon.com', $headers['host']);
        $this->assertEquals('text/html,application/json;q=1', $headers['accept']);
        $this->assertEquals('keep-alive', $headers['connection']);
        $this->assertEquals('gzip,deflate,sdch', $headers['accept-encoding']);
        $this->assertEquals('application/json', $headers['content-type']);
        $this->assertEquals('32', $headers['content-length']);
    }

    public function testNormalizePath()
    {
        $this->assertEquals('a/c', HttpUtils::normalizePath('a/c'));
        $this->assertEquals('a/c', HttpUtils::normalizePath('a//c'));
        $this->assertEquals('a/c', HttpUtils::normalizePath('a/c/.'));
        $this->assertEquals('a/c', HttpUtils::normalizePath('a/c/b/..'));
        $this->assertEquals('a/c/', HttpUtils::normalizePath('a/c/'));
        $this->assertEquals('/a/c', HttpUtils::normalizePath('/../a/c'));
        $this->assertEquals('/a/c', HttpUtils::normalizePath('/../a/b/../././/c'));
    }

    public function testNormalizeEmptyPath()
    {
        $this->assertEquals('/', HttpUtils::normalizePath(''));
        $this->assertEquals('/', HttpUtils::normalizePath(null));
    }
}
