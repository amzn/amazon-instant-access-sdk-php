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

/**
 * @internal
 *
 * Contains utility functions related to http
 */
class HttpUtils
{
    const X_AMZ_DATE_HEADER = 'x-amz-date';
    const AUTHORIZATION_HEADER = 'authorization';

    /**
     * A wrapper to the header function.
     *
     * If UNIT_TESTING is defined, this function does not do anything, this allows us to unit test functions that
     * use the header function. Otherwise, we get 'Cannot modify header information - headers already sent'.
     *
     * @param string $string The header string.
     * @param boolean $replace Indicates whether the header should replace a previous similar header.
     * @param int $http_response_code Forces the HTTP response code to the specified value.
     */
    public static function setResponseHeader($string, $replace = true, $http_response_code = null)
    {
        if (!defined('UNIT_TESTING')) {
            header($string, $replace, $http_response_code);
        }
    }

    /**
     * Parse a $_SERVER like array with the request information and return a full URL
     *
     * @param array $server the $_SERVER array
     *
     * @return string the request url
     */
    public static function parseFullURL(array $server)
    {
        $ssl = false;
        if (!empty($server['HTTPS']) && $server['HTTPS'] != 'off') {
            $ssl = true;
        }

        $protocol = strtolower($server['SERVER_PROTOCOL']);
        $protocol = substr($protocol, 0, strpos($protocol, '/'));
        if ($ssl) {
            $protocol .= 's';
        }

        $port = $server['SERVER_PORT'];
        if ((!$ssl && $port != '80') || ($ssl && $port != '443')) {
            $port = ':' . $port;
        } else {
            $port = '';
        }

        if (isset($server['HTTP_X_FORWARDED_HOST'])) {
            $host = $server['HTTP_X_FORWARDED_HOST'];
        } elseif (isset($server['HTTP_HOST'])) {
            $host = $server['HTTP_HOST'];
        } else {
            $host = $server['SERVER_NAME'];
        }

        return $protocol . '://' . $host . $port . $server['REQUEST_URI'];
    }

    /**
     * Parse a $_SERVER like array with request information and return the http headers.
     *
     * Http headers have keys with prefix 'HTTP_', except content type and content length. This function traverses
     * the array, checks if the element is a http header, replaces '_' by '-' and convert the name to lower case.
     *
     * @param array $server the $_SERVER array
     *
     * @return array an array containing the http headers
     */
    public static function parseRequestHeaders(array $server)
    {
        $headers = array();

        foreach ($server as $key => $value) {
            if (substr($key, 0, 5) == 'HTTP_') {
                $key = substr($key, 5);
            } elseif ($key != 'CONTENT_TYPE' && $key != 'CONTENT_LENGTH') {
                continue;
            }

            $header = str_replace('_', '-', strtolower($key));
            $headers[$header] = $value;
        }

        return $headers;
    }

    /**
     * Normalize the URL according to RFC 3986.
     *
     * This function replaces multiple slashses with a single one and eliminates '.' (current directory) and
     * '..' (parent directory).
     *
     * @param string a path
     *
     * @return string a normalized path
     */
    public static function normalizePath($path)
    {
        if (!$path) {
            return '/';
        }

        $parts = explode('/', $path);

        $normalizedParts = array();
        foreach ($parts as $part) {
            if ($part == '..') {
                array_pop($normalizedParts);
            } elseif ($part != '.' && $part != '') {
                $normalizedParts[] = $part;
            }
        }

        $newPath = '';

        if ($path[0] == '/') {
            $newPath .= '/';
        }

        $newPath .= implode('/', $normalizedParts);

        if ($path != '/' && end($parts) == '') {
            $newPath .= '/';
        }

        return $newPath;
    }
}
