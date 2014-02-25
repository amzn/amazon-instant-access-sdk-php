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

use Amazon\InstantAccess\Utils\IOUtils;

class CredentialStoreTest extends \PHPUnit_Framework_TestCase
{
    private static $KEYS = array('69b2048d-8bf8-4c1c-b49d-e6114897a9a5', 'dce53190-1f70-4206-ad28-0e1ab3683161',
                                 'f0a2586d-24ea-432f-a833-2da18f15ebd4', 'eb3ce251-ef76-48ee-abb0-5886b1a3dfa0',
                                 '7568ccc2-9881-4468-ad73-025d16f0662e', '5de206ab-3a06-4354-a9a4-bfd6efee8027');

    private static $INVALID_KEY = '871dbe31-3b46-4ca5-b9a2-8ad78eac4a4f';

    private static $VALID_FILE;
    private static $INVALID_FILE;

    public static function setUpBeforeClass()
    {
        self::$VALID_FILE = tmpfile();
        $content = self::$KEYS[0] . ' ' . self::$KEYS[1] . "\n";
        $content .= self::$KEYS[2] . ' ' . self::$KEYS[3] . "\n";
        $content .= self::$KEYS[4] . ' ' . self::$KEYS[5] . "\n";

        fwrite(self::$VALID_FILE, $content);
        fseek(self::$VALID_FILE, 0);

        self::$INVALID_FILE = tmpfile();
        $content = self::$KEYS[0] . self::$KEYS[1] . "\n";

        fwrite(self::$INVALID_FILE, $content);
        fseek(self::$INVALID_FILE, 0);
    }

    public function testLoadFromFile()
    {
        $store = new CredentialStore();
        $store->loadFromFile(IOUtils::getFilePathFromHandle(self::$VALID_FILE));

        $this->assertCorrectCredentials($store);
    }

    public function testLoad()
    {
        $store = new CredentialStore();

        $contents = file_get_contents(IOUtils::getFilePathFromHandle(self::$VALID_FILE));
        $store->load($contents);

        $this->assertCorrectCredentials($store);
    }

    public function testLoadFromEmptyString()
    {
        $this->setExpectedException('InvalidArgumentException');

        $store = new CredentialStore();
        $store->load(null);
    }

    public function testLoadFromInvalidFile()
    {
        $this->setExpectedException('InvalidArgumentException');

        $store = new CredentialStore();
        $store->loadFromFile(IOUtils::getFilePathFromHandle(self::$INVALID_FILE));
    }

    public function testLoadFromEmptyFile()
    {
        $this->setExpectedException('InvalidArgumentException');

        $store = new CredentialStore();
        $store->loadFromFile(null);
    }

    public function testGetInvalidCredential()
    {
        $store = new CredentialStore();
        $store->loadFromFile(IOUtils::getFilePathFromHandle(self::$VALID_FILE));
        $credential = $store->get(self::$INVALID_KEY);

        $this->assertNull($credential);
    }

    public function testAddCredential()
    {
        $store = new CredentialStore();
        $store->add(new Credential(self::$KEYS[0], self::$KEYS[1]));

        $credential = $store->get(self::$KEYS[1]);

        $this->assertEquals(self::$KEYS[0], $credential->getSecretKey());
        $this->assertEquals(self::$KEYS[1], $credential->getPublicKey());
    }

    public function testRemoveCredential()
    {
        $store = new CredentialStore();
        $store->add(new Credential(self::$KEYS[0], self::$KEYS[1]));

        $this->assertEquals(1, count($store->getAll()));

        $store->remove(self::$KEYS[1]);

        $this->assertEquals(0, count($store->getAll()));
    }

    private function assertCorrectCredentials($store)
    {
        $this->assertEquals(self::$KEYS[0], $store->get(self::$KEYS[1])->getSecretKey());
        $this->assertEquals(self::$KEYS[1], $store->get(self::$KEYS[1])->getPublicKey());

        $this->assertEquals(self::$KEYS[2], $store->get(self::$KEYS[3])->getSecretKey());
        $this->assertEquals(self::$KEYS[3], $store->get(self::$KEYS[3])->getPublicKey());

        $this->assertEquals(self::$KEYS[4], $store->get(self::$KEYS[5])->getSecretKey());
        $this->assertEquals(self::$KEYS[5], $store->get(self::$KEYS[5])->getPublicKey());
    }
}
