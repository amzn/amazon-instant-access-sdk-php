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
 * Serializable request object used to activate a subscription for a specified user and product
 */
class SubscriptionActivateRequest extends InstantAccessRequest
{
    /** @var string */
    protected $subscriptionId;
    /** @var string */
    protected $productId;
    /** @var string */
    protected $userId;
    /** @var string */
    protected $subscriptionGroupId;
    /** @var integer */
    protected $numberOfSubscriptionsInGroup;

    /**
     * Create a new object from a JSON string.
     *
     * @param string $jsonString a string containing the JSON representation of the object
     * @param Closure $callback an optional callback function that is called when creating the new object
     *
     * @return SubscriptionActivateRequest a new object created from the JSON string
     */
    public static function createFromJson($jsonString, \Closure $callback = null)
    {
        $callback = function ($newObject, $jsonObject) {
            $newObject->setSubscriptionId($jsonObject->subscriptionId);
            $newObject->setProductId($jsonObject->productId);
            $newObject->setUserId($jsonObject->userId);
            if (isset($jsonObject->subscriptionGroupId)) {
                $newObject->setSubscriptionGroupId($jsonObject->subscriptionGroupId);
            }
            if (isset($jsonObject->numberOfSubscriptionsInGroup)) {
                $newObject->setNumberOfSubscriptionsInGroup($jsonObject->numberOfSubscriptionsInGroup);
            }
        };

        $object = parent::createFromJson($jsonString, $callback);

        return $object;
    }

    public function getSubscriptionId()
    {
        return $this->subscriptionId;
    }

    public function setSubscriptionId($subscriptionId)
    {
        $this->subscriptionId = $subscriptionId;
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

    public function getUserId()
    {
        return $this->userId;
    }

    public function setUserId($userId)
    {
        $this->userId = $userId;
        return $this;
    }

    public function getSubscriptionGroupId()
    {
        return $this->subscriptionGroupId;
    }

    public function setSubscriptionGroupId($subscriptionGroupId)
    {
        $this->subscriptionGroupId = $subscriptionGroupId;
        return $this;
    }

    public function getNumberOfSubscriptionsInGroup()
    {
        return $this->numberOfSubscriptionsInGroup;
    }

    public function setNumberOfSubscriptionsInGroup($numberOfSubscriptionsInGroup)
    {
        $this->numberOfSubscriptionsInGroup = $numberOfSubscriptionsInGroup;
        return $this;
    }
}
