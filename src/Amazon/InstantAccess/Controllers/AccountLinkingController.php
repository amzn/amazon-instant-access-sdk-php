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
use Amazon\InstantAccess\Serialization\GetUserIdRequest;
use Amazon\InstantAccess\Serialization\GetUserIdResponse;

/**
 * This class is used to implement the Account Linking section of Instant Access API.
 *
 * When creating this controller a valid credential store must be passed that will be used to verify the message
 * authenticity.
 *
 * A callback function must be passed to {@link AccountLinkingController::onGetUserId()}, the callback is
 * called when the {@link Controller::process()} method is invoked.
 */
class AccountLinkingController extends Controller
{
    /** @var \Closure */
    private $getUserIdCallback;

    /**
     * Set the callback function for GetUserId
     *
     * @param \Closure $callback a callable object that receives a {@link GetUserIdRequest} object and returns
     * a {@link GetUserIdResponse}
     */
    public function onGetUserId(\Closure $callback)
    {
        $this->getUserIdCallback = $callback;
    }

    protected function processOperation($operation)
    {
        switch ($operation) {
            case InstantAccessOperationValue::GET_USER_ID:
                $callback = $this->getUserIdCallback;
                $request = GetUserIdRequest::createFromJson($this->request->getBody());
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
