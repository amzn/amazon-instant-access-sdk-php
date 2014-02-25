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

use Amazon\InstantAccess\Utils\HttpUtils;

/**
 * @internal
 *
 * Class that represents an HTTP request.
 *
 * This is used for verifying a request's signature.
 */
class Request
{
    /** @var string */
    private $url;
    /** @var string */
    private $method;
    /** @var string */
    private $body;
    /** @var array */
    private $headers;

    /**
     * Creates a new object from $_SERVER and the message body.
     *
     * Used for verifying the signature of a request.
     *
     * @param array $server the $_SERVER array
     * @param string|null $requestBody the contents of the body of the request
     */
    public function __construct(array $server, $requestBody = '')
    {
        $this->url = HttpUtils::parseFullURL($server);
        $this->method = $server['REQUEST_METHOD'];
        $this->headers = HttpUtils::parseRequestHeaders($server);
        $this->body = $requestBody;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function &getHeaders()
    {
        return $this->headers;
    }

    /**
     * Remove headers that are present in $filter
     *
     * @param array $filter array of headers to be removed from this request
     */
    public function filterHeaders(array $filter)
    {
        $this->headers = array_diff_key($this->headers, array_flip($filter));
    }

    /**
     * @return string a human-friendly string representation of the Request
     */
    public function __tostring()
    {
        $headersStr = implode(
            ', ',
            array_map(
                function ($v, $k) {
                    return sprintf("%s:'%s'", $k, $v);
                },
                $this->getHeaders(),
                array_keys($this->getHeaders())
            )
        );

        $bodyStr = trim(preg_replace('/\s+/', ' ', $this->getBody()));

        return sprintf(
            'Method: %s, Url: %s, Headers: %s, Body: %s',
            $this->getMethod(),
            $this->getMethod(),
            $headersStr,
            $bodyStr
        );
    }
}
