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
 * An enumeration of values that can be used as a reason for a subscription deactivate request
 *
 * @see Amazon\InstantAccess\Serialization\SubscriptionDeactivateRequest
 */
abstract class SubscriptionDeactivateReasonValue extends Enum
{
    const NOT_RENEWED              = 'NOT_RENEWED';
    const USER_REQUEST             = 'USER_REQUEST';
    const CUSTOMER_SERVICE_REQUEST = 'CUSTOMER_SERVICE_REQUEST';
    const PAYMENT_PROBLEM          = 'PAYMENT_PROBLEM';
    const UNABLE_TO_FULFILL        = 'UNABLE_TO_FULFILL';
    const TESTING                  = 'TESTING';
}
