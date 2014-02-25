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

class AuthorizationHeaderTest extends \PHPUnit_Framework_TestCase
{
    public function testParseValidHeader()
    {
        $value = 'DTA1-HMAC-SHA256 ' .
                 'SignedHeaders=aaa;content-type;x-amz-date;zzz, ' .
                 'Credential=KEYID/20110909, ' .
                 'Signature=87729cb3475859a18b5d9cead0bba82f0f56a85c2a13bed3bc229c6c35e06628';

        $header = AuthorizationHeader::parse($value);

        $this->assertNotNull($header);
        $this->assertEquals('DTA1-HMAC-SHA256', $header->getAlgorithm());
        $this->assertEquals(array('key' => 'KEYID', 'date' =>'20110909'), $header->getCredential());
        $this->assertEquals(array('aaa', 'content-type', 'x-amz-date', 'zzz'), $header->getSignedHeaders());
        $this->assertEquals('87729cb3475859a18b5d9cead0bba82f0f56a85c2a13bed3bc229c6c35e06628', $header->getSignature());
    }

    public function testParseValidHeader2()
    {
        $value = 'DTA1-HMAC-SHA256 ' .
                 'SignedHeaders=Content-Type;X-Amz-Date;X-Amz-Dta-Version;X-AMZ-REQUEST-ID, ' .
                 'Credential=367caa91-cde5-48f2-91fe-bb95f546e9f0/20131207, ' .
                 'Signature=6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe';

        $header = AuthorizationHeader::parse($value);

        $this->assertNotNull($header);
        $this->assertEquals('DTA1-HMAC-SHA256', $header->getAlgorithm());
        $this->assertEquals(array('key' => '367caa91-cde5-48f2-91fe-bb95f546e9f0', 'date' =>'20131207'), $header->getCredential());
        $this->assertEquals(array('content-type', 'x-amz-date', 'x-amz-dta-version', 'x-amz-request-id'), $header->getSignedHeaders());
        $this->assertEquals('6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe', $header->getSignature());
    }

    public function testParseInvalidHeader()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $value = '';

        $header = AuthorizationHeader::parse($value);
    }

    public function testParseInvalidHeaderFormat()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $value = 'SignedHeaders=content-type;x-amz-date;x-amz-dta-version;x-amz-request-id, ' .
                 'Credential=367caa91-cde5-48f2-91fe-bb95f546e9f0, ' .
                 'Signature=6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe';

        $header = AuthorizationHeader::parse($value);
    }

    public function testParseInvalidHeaderinvalidCredential()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $value = 'DTA1-HMAC-SHA256 ' .
                 'SignedHeaders=content-type;x-amz-date;x-amz-dta-version;x-amz-request-id, ' .
                 'Credential=367caa91-cde5-48f2-91fe-bb95f546e9f0, ' .
                 'Signature=6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe';

        $header = AuthorizationHeader::parse($value);
    }

    public function testToString()
    {
        $header = new AuthorizationHeader(
            'DTA1-HMAC-SHA256',
            array('content-type', 'x-amz-date', 'x-amz-dta-version', 'x-amz-request-id'),
            array('key' => '367caa91-cde5-48f2-91fe-bb95f546e9f0', 'date' => '20140101'),
            '6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe'
        );

        $str = 'DTA1-HMAC-SHA256 ' .
                 'SignedHeaders=content-type;x-amz-date;x-amz-dta-version;x-amz-request-id, ' .
                 'Credential=367caa91-cde5-48f2-91fe-bb95f546e9f0/20140101, ' .
                 'Signature=6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe';

        $this->assertEquals($str, (string)$header);
    }

    public function testCreateAuthorixationHeaderWithInvalidAlgorithm()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $header = new AuthorizationHeader(
            '',
            array('content-type', 'x-amz-date', 'x-amz-dta-version', 'x-amz-request-id'),
            array('key' => '367caa91-cde5-48f2-91fe-bb95f546e9f0', 'date' => '20140101'),
            '6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe'
        );
    }

    public function testCreateAuthorixationHeaderWithInvalidHeaders()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $header = new AuthorizationHeader(
            'DTA1-HMAC-SHA256',
            'test',
            array('key' => '367caa91-cde5-48f2-91fe-bb95f546e9f0', 'date' => '20140101'),
            '6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe'
        );
    }

    public function testCreateAuthorixationHeaderWithInvalidCredential()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $header = new AuthorizationHeader(
            'DTA1-HMAC-SHA256',
            array('content-type', 'x-amz-date', 'x-amz-dta-version', 'x-amz-request-id'),
            array('key' => '367caa91-cde5-48f2-91fe-bb95f546e9f0'),
            '6fe5d5bbf4acda9b0f47f66db3ad8f23a33117ee52b45ae69983bec0b50550fe'
        );
    }

    public function testCreateAuthorixationHeaderWithInvalidSignature()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $header = new AuthorizationHeader(
            'DTA1-HMAC-SHA256',
            array('content-type', 'x-amz-date', 'x-amz-dta-version', 'x-amz-request-id'),
            array('key' => '367caa91-cde5-48f2-91fe-bb95f546e9f0', 'date' => '20140101'),
            ''
        );
    }
}
