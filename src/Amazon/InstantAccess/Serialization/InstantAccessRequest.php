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

use Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue;

/**
 * The parent class of all serializable request classes.
 *
 * @see Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue For operation values
 */
class InstantAccessRequest
{
    /** @var string */
    protected $operation;

    /**
     * Create a new object from a JSON string.
     *
     * @param string $jsonString a string containing the JSON representation of the object
     * @param Closure $callback an optional callback function that is called when creating the new object
     *
     * @return InstantAccessRequest a new object created from the JSON string
     */
    public static function createFromJson($jsonString, \Closure $callback = null)
    {
        if (is_string($jsonString)) {
            $jsonObject = json_decode($jsonString);
        }

        if (!$jsonObject) {
            throw new \InvalidArgumentException("Invalid json string.");
        }

        try {
            // get class type
            $type = get_called_class();
            // creates new object of the type being deserialized
            $newObject = new $type;
            // sets the operation
            $newObject->setOperation($jsonObject->operation);
            // and the specific fields
            if ($callback && is_callable($callback)) {
                $callback($newObject, $jsonObject);
            }
        } catch (\Exception $e) {
            throw new \InvalidArgumentException(
                sprintf('Unable to deserialized object: %s. %s', $type, $e->getMessage())
            );
        }

        return $newObject;
    }

    public function getOperation()
    {
        return $this->operation;
    }

    /**
     * Set the request operation
     *
     * @param string a string representation of the operation
     *
     * @see Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue For operation values
     */
    public function setOperation($operation)
    {
        if (!InstantAccessOperationValue::isValid($operation)) {
            throw new \InvalidArgumentException(sprintf('Invalid operation value: %s', $reason));
        }

        $this->operation = $operation;
        return $this;
    }
}
