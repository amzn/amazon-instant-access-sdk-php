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

use Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivatePeriodValue;
use Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivateReasonValue;

/**
 * Serializable request object used to deactivate a subscription
 *
 * @see Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivatePeriodValue For period values
 * @see Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivateReasonValue For reason values
 */
class SubscriptionDeactivateRequest extends InstantAccessRequest
{
    /** @var string */
    protected $subscriptionId;
    /** @var string */
    protected $reason;
    /** @var string */
    protected $period;

    /**
     * Create a new object from a JSON string.
     *
     * @param string $jsonString a string containing the JSON representation of the object
     * @param Closure $callback an optional callback function that is called when creating the new object
     *
     * @return SubscriptionDeactivateRequest a new object created from the JSON string
     */
    public static function createFromJson($jsonString, \Closure $callback = null)
    {
        $callback = function ($newObject, $jsonObject) {
            $newObject->setSubscriptionId($jsonObject->subscriptionId);
            $newObject->setReason($jsonObject->reason);
            $newObject->setPeriod($jsonObject->period);
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

    public function getReason()
    {
        return $this->reason;
    }

    /**
     * Set the request reason
     *
     * @param string a string representation of the reason
     *
     * @see Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivateReasonValue For reason values
     */
    public function setReason($reason)
    {
        if (!SubscriptionDeactivateReasonValue::isValid($reason)) {
            throw new \InvalidArgumentException(sprintf('Invalid reason value: %s', $reason));
        }

        $this->reason = $reason;
        return $this;
    }

    public function getPeriod()
    {
        return $this->period;
    }

    /**
     * Set the request period
     *
     * @param string a string representation of the period
     *
     * @see Amazon\InstantAccess\Serialization\Enums\SubscriptionDeactivatePeriodValue For period values
     */
    public function setPeriod($period)
    {
        if (!SubscriptionDeactivatePeriodValue::isValid($period)) {
            throw new \InvalidArgumentException(sprintf('Invalid period value: %s', $reason));
        }

        $this->period = $period;
        return $this;
    }
}
