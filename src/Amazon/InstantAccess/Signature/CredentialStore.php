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
 * Class that is used to manage multiple credentials.
 *
 * The {@link CredentialStore::load()} method can be called to load keys from a file path or a string.
 *
 * Each line of the file/string must contain a secret key and a public key separated by an empty space, for
 * example:
 *
 * 69b2048d-8bf8-4c1c-b49d-e6114897a9a5 dce53190-1f70-4206-ad28-0e1ab3683161
 *
 * Credentials, then, can be accessed by the public key using {@link CredentialStore::get()}
 *
 */
class CredentialStore
{
    /** @var array */
    private $store = array();

    /**
     * Gets the credential for a given public key.
     *
     * @param string $publicKey the public key
     *
     * @return Credential the credential if present in the store, null otherwise
     */
    public function get($publicKey)
    {
        if (empty($publicKey) || !array_key_exists($publicKey, $this->store)) {
            return null;
        }

        return $this->store[$publicKey];
    }

    /**
     * Gets the credentials stored in this store.
     *
     * @return array an array with all the credentials
     */
    public function getAll()
    {
        return $this->store;
    }

    /**
     * Adds the new credential to the store. If the store already contains the public key, the credential is replaced.
     *
     * @param Credential credential the credential object to be added
     */
    public function add(Credential $credential)
    {
        $this->store[$credential->getPublicKey()] = $credential;
    }

    /**
     * Removes the credential from the store.
     *
     * @param string $publicKey the public key of the credential to be removed
     */
    public function remove($publicKey)
    {
        unset($this->store[$publicKey]);
    }

    /**
     * Loads keys from a file and populates the store.
     *
     * Each line of the file must contain a secret key and a public key separated by an empty space.
     *
     * @param string $filePath the path of the file that contains the keys
     *
     * @throws InvalidArgumentException if the file does not exist
     */
    public function loadFromFile($filePath)
    {
        if (empty($filePath) || !file_exists($filePath)) {
            $message = 'Invalid keys file path';
            throw new \InvalidArgumentException($message);
        }

        $contents = file_get_contents($filePath);

        $this->load($contents);
    }

    /**
     * Loads keys from a string and populates the store.
     *
     * Each line of the file must contain a secret key and a public key separated by an empty space.
     *
     * @param string $contents the string object that contains the keys
     *
     * @throws InvalidArgumentException if the content is empty or malformed
     */
    public function load($contents)
    {
        if (empty($contents)) {
            $message = 'Empty key container';
            throw new \InvalidArgumentException($message);
        }

        $lines = explode(PHP_EOL, $contents);

        foreach ($lines as $i => $line) {
            // Ignore blank lines in between credentials
            if (!$line) {
                continue;
            }

            // credentials should be separate by an empty space
            $keys = preg_split('/\s+/', $line);

            // Invalid format
            if (count($keys) < 2) {
                $message = 'Invalid credentials format found on line ' . $i;
                throw new \InvalidArgumentException($message);
            }

            $secretKey = $keys[0];
            $publicKey = $keys[1];

            $this->store[$publicKey] = new Credential($secretKey, $publicKey);
        }
    }
}
