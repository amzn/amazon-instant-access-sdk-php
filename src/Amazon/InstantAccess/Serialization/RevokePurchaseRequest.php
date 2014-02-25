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

use Amazon\InstantAccess\Serialization\Enums\RevokePurchaseReasonValue;

/**
 * Serializable response object used to fulfill a purchase
 *
 * @see Amazon\InstantAccess\Serialization\Enums\RevokePurchaseReasonValue For reason values
 */
class RevokePurchaseRequest extends InstantAccessRequest
{
    /** @var string */
    protected $purchaseToken;
    /** @var string */
    protected $userId;
    /** @var string */
    protected $productId;
    /** @var string */
    protected $reason;

    /**
     * Create a new object from a JSON string.
     *
     * @param string $jsonString a string containing the JSON representation of the object
     * @param Closure $callback an optional callback function that is called when creating the new object
     *
     * @return RevokePurchaseRequest a new object created from the JSON string
     */
    public static function createFromJson($jsonString, \Closure $callback = null)
    {
        $callback = function ($newObject, $jsonObject) {
            $newObject->setPurchaseToken($jsonObject->purchaseToken);
            $newObject->setUserId($jsonObject->userId);
            $newObject->setProductId($jsonObject->productId);
            $newObject->setReason($jsonObject->reason);
        };

        $object = parent::createFromJson($jsonString, $callback);

        return $object;
    }

    public function getPurchaseToken()
    {
        return $this->purchaseToken;
    }

    public function setPurchaseToken($purchaseToken)
    {
        $this->purchaseToken = $purchaseToken;
        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function getProductId()
    {
        return $this->productId;
    }

    public function setProductId($productId)
    {
        $this->productId = $productId;
        return $this;
    }

    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set the request reason
     *
     * @param string a string representation of the reason
     *
     * @see Amazon\InstantAccess\Serialization\Enums\RevokePurchaseReasonValue For reason values
     */
    public function setReason($reason)
    {
        if (!RevokePurchaseReasonValue::isValid($reason)) {
            throw new \InvalidArgumentException(sprintf('Invalid reason value: %s', $reason));
        }

        $this->reason = $reason;
        return $this;
    }
}
