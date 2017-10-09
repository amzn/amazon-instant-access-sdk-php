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
// @codingStandardsIgnoreFile
require dirname(__DIR__) . '/build/instant-access-sdk-php.phar';

if (!defined('UNIT_TESTING')) define('UNIT_TESTING', true);

// configure logger
$monolog = new Monolog\Logger('AmazonInstantAccessTest');
$monolog->pushHandler(new Monolog\Handler\StreamHandler('build/log/test-phar-output.log', Monolog\Logger::DEBUG));
Amazon\InstantAccess\Log\Logger::setLogger($monolog);
date_default_timezone_set('UTC');
