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

use Amazon\InstantAccess\Serialization\Enums\GetUserIdResponseValue;

/**
 * Serializable response object used to link an account
 *
 * @see Amazon\InstantAccess\Serialization\Enums\GetUserIdResponseValue For response values
 */
class GetUserIdResponse extends InstantAccessResponse
{
    /** @var string */
    protected $userId;

    /**
     * Set the response content
     *
     * @param string a string representation of the response
     *
     * @see Amazon\InstantAccess\Serialization\Enums\GetUserIdResponseValue For response values
     */
    public function setResponse($response)
    {
        if (!GetUserIdResponseValue::isValid($response)) {
            throw new \InvalidArgumentException(sprintf('Invalid response value: %s', $response));
        }

        $this->response = $response;
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
}
