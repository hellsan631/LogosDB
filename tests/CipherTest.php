<?php

class CipherTest extends PHPUnit_Framework_TestCase{

    public function setUp(){
        $this->cipher = new Cipher("s3cur3k3y");
    }

    public function testCheckCipherEncrypt(){
        $this->assertEquals("kRTIR6qDGYNumkoAMfwWMGNVPIUoODr0kvFMCmPDynM=",  $this->cipher->encrypt("Hello World!"));
    }

    public function testCheckCipherDecrypt(){
        $this->assertEquals("Hello World!",  $this->cipher->decrypt("kRTIR6qDGYNumkoAMfwWMGNVPIUoODr0kvFMCmPDynM="));
    }

    public function testRandomKeyGen(){
        $this->assertNotEquals(Cipher::getRandomKey(),  Cipher::getRandomKey());
    }

}