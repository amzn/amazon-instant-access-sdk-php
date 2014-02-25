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
 * An enumeration of values that can be send as a response to a subscription activate request
 *
 * @see Amazon\InstantAccess\Serialization\SubscriptionActivateResponse
 */
abstract class SubscriptionActivateResponseValue extends Enum
{
    const OK                        = 'OK';
    const FAIL_USER_INVALID         = 'FAIL_USER_INVALID';
    const FAIL_USER_NOT_ELIGIBLE    = 'FAIL_USER_NOT_ELIGIBLE';
    const FAIL_OTHER                = 'FAIL_OTHER';
}
