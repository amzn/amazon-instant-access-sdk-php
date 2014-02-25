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

/**
 * Abstract class representing a Enum where values are defined as constants.
 */
abstract class Enum
{
    private static function getConstants()
    {
        $reflect = new \ReflectionClass(get_called_class());
        return $reflect->getConstants();
    }

    /**
     * Check if $name is a value supported by the enum
     *
     * @param string $name a string
     * @return boolean true if $name is a valid value in this enum, false otherwise
     */
    public static function isValid($name)
    {
        return in_array($name, self::getConstants());
    }
}
