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
namespace Amazon\InstantAccess\Signature;

/**
 * Class representing a signing credential.
 */
class Credential
{
    /** @var string */
    private $secretKey;
    /** @var string */
    private $publicKey;

    public function __construct($secretKey, $publicKey)
    {
        if (empty($secretKey) || empty($publicKey)) {
            throw new \InvalidArgumentException('Invalid credential data.');
        }

        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
    }

    /**
     * Gets the secret key.
     *
     * @return string the secret key
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

    /**
     * Gets the public key.
     *
     * @return string the public key
     */
    public function getPublicKey()
    {
        return $this->publicKey;
    }
}
