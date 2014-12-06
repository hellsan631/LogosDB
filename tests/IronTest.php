<?php

class IronTest extends PHPUnit_Framework_TestCase{

    public function setUp(){
        $this->iron = Iron::getInstance();
    }

    public function testCheckTokenSession(){
        $this->assertEquals($_SESSION['token_id'],  $this->iron->get_token_id());
        $this->assertEquals($_SESSION[$this->iron->get_token_id()],  $this->iron->get_token_value());
    }

}