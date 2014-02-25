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

Phar::mapPhar('instant-access-sdk-php.phar');

require_once 'phar://instant-access-sdk-php.phar/vendor/symfony/class-loader/Symfony/Component/ClassLoader/UniversalClassLoader.php';

$classLoader = new Symfony\Component\ClassLoader\UniversalClassLoader();
$classLoader->registerNamespaces(array(
    'Amazon'  => 'phar://instant-access-sdk-php.phar/src',
    'Psr'     => 'phar://instant-access-sdk-php.phar/vendor/psr/log',
    'Monolog' => 'phar://instant-access-sdk-php.phar/vendor/monolog/monolog/src'
));
$classLoader->register();

return $classLoader;

__HALT_COMPILER();
