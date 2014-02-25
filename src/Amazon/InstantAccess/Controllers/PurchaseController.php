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
namespace Amazon\InstantAccess\Controllers;

use Amazon\InstantAccess\Serialization\Enums\InstantAccessOperationValue;
use Amazon\InstantAccess\Serialization\FulfillPurchaseRequest;
use Amazon\InstantAccess\Serialization\RevokePurchaseRequest;
use Amazon\InstantAccess\Serialization\SubscriptionActivateRequest;
use Amazon\InstantAccess\Serialization\SubscriptionDeactivateRequest;

/**
 * This class is used to implement the Purchase/Subscription section of Instant Access API.
 *
 * When creating this controller a valid credential store must be passed that will be used to verify the message
 * authenticity.
 *
 * Callbacks can be set through:
 * <br/>
 * {@link PurchaseController::onFulfillPurchase()} <br/>
 * {@link PurchaseController::onRevokePurchase()} <br/>
 * {@link PurchaseController::onSubscriptionActivate()} <br/>
 * {@link PurchaseController::onSubscriptionDeactivate()} <br/>
 * <br/>
 * The callbacks are called when the {@link Controller::process()} method is invoked.
 */
class PurchaseController extends Controller
{
    /** @var \Closure */
    private $fulfillPurchaseCallback;
    /** @var \Closure */
    private $revokePurchaseCallback;
    /** @var \Closure */
    private $subscriptionActivateCallback;
    /** @var \Closure */
    private $subscriptionDeactivateCallback;

    /**
     * Set the callback function for fulfill purchase
     *
     * @param \Closure $callback a callable object that receives a {@link FulfillPurchaseRequest} object
     * and returns a {@link FulfillPurchaseResponse}
     */
    public function onFulfillPurchase(\Closure $callback)
    {
        $this->fulfillPurchaseCallback = $callback;
    }

    /**
     * Set the callback function for revoke purchase
     *
     * @param \Closure $callback a callable object that receives a {@link RevokePurchaseRequest} object
     * and returns a {@link RevokePurchaseResponse}
     */
    public function onRevokePurchase(\Closure $callback)
    {
        $this->revokePurchaseCallback = $callback;
    }

    /**
     * Set the callback function for subscription activate
     *
     * @param \Closure $callback a callable object that receives a {@link SubscriptionActivateRequest} object
     * and returns a {@link SubscriptionActivateResponse}
     */
    public function onSubscriptionActivate(\Closure $callback)
    {
        $this->subscriptionActivateCallback = $callback;
    }

    /**
     * Set the callback function for subscription deactivate
     *
     * @param \Closure $callback a callable object that receives a {@link SubscriptionDeactivateRequest} object
     * and returns a {@link SubscriptionDeactivateResponse}
     */
    public function onSubscriptionDeactivate(\Closure $callback)
    {
        $this->subscriptionDeactivateCallback = $callback;
    }

    protected function processOperation($operation)
    {
        switch ($operation) {
            case InstantAccessOperationValue::PURCHASE:
                $callback = $this->fulfillPurchaseCallback;
                $request = FulfillPurchaseRequest::createFromJson($this->request->getBody());
                break;
            case InstantAccessOperationValue::REVOKE:
                $callback = $this->revokePurchaseCallback;
                $request = RevokePurchaseRequest::createFromJson($this->request->getBody());
                break;
            case InstantAccessOperationValue::SUBSCRIPTION_ACTIVATE:
                $callback = $this->subscriptionActivateCallback;
                $request = SubscriptionActivateRequest::createFromJson($this->request->getBody());
                break;
            case InstantAccessOperationValue::SUBSCRIPTION_DEACTIVATE:
                $callback = $this->subscriptionDeactivateCallback;
                $request = SubscriptionDeactivateRequest::createFromJson($this->request->getBody());
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('Operation %s is not supported by %s', $operation, get_class($this))
                );
        }

        if (!$callback) {
            throw new \UnexpectedValueException(sprintf('Callback not set for %s', $operation));
        }

        $iaResponse = $callback($request);

        return $iaResponse;
    }
}
