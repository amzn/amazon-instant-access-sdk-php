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
namespace Amazon\InstantAccess\Log;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class used to log events that happen whitin the application.
 *
 * @see Logger::setLogger($logger) to set a PSR-3 compliant logger.
 */
class Logger
{
    private static $logger;

    /**
     * Set a PSR-3 compliant logger object.
     *
     * @param LoggerInterface a PSR-3 compliant {@link LoggerInterface} object
     */
    public static function setLogger(LoggerInterface $logger)
    {
        self::$logger = $logger;
    }

    /**
     * Get the current logger.
     *
     * Defaults to {@link NullLogger}
     *
     * @return LoggerInterface the current logger
     */
    public static function getLogger()
    {
        // set the default Logger
        if (!self::$logger) {
            self::$logger = new NullLogger();
        }

        return self::$logger;
    }
}
