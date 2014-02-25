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

/**
 * * Serializable request object used to link an account
 */
class GetUserIdRequest extends InstantAccessRequest
{
    /** @var string */
    protected $infoField1;
    /** @var string */
    protected $infoField2;
    /** @var string */
    protected $infoField3;

    /**
     * Create a new object from a JSON string.
     *
     * @param string $jsonString a string containing the JSON representation of the object
     * @param Closure $callback an optional callback function that is called when creating the new object
     *
     * @return GetUserIdRequest a new object created from the JSON string
     */
    public static function createFromJson($jsonString, \Closure $callback = null)
    {
        $callback = function ($newObject, $jsonObject) {
            $newObject->setInfoField1($jsonObject->infoField1);

            // optional field
            if (isset($jsonObject->infoField2)) {
                $newObject->setInfoField2($jsonObject->infoField2);
            }

            // optional field
            if (isset($jsonObject->infoField3)) {
                $newObject->setInfoField3($jsonObject->infoField3);
            }
        };

        $object = parent::createFromJson($jsonString, $callback);

        return $object;
    }

    public function getInfoField1()
    {
        return $this->infoField1;
    }

    public function setInfoField1($infoField1)
    {
        $this->infoField1 = $infoField1;
        return $this;
    }

    public function getInfoField2()
    {
        return $this->infoField2;
    }

    public function setInfoField2($infoField2)
    {
        $this->infoField2 = $infoField2;
        return $this;
    }

    public function getInfoField3()
    {
        return $this->infoField3;
    }

    public function setInfoField3($infoField3)
    {
        $this->infoField3 = $infoField3;
        return $this;
    }
}
