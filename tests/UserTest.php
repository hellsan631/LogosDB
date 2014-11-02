<?php

class UserTest extends Generic_DB_Test{

    public function setUp(){
        $this->user = new User();
    }

    public function getDataSet(){
        return $this->createXMLDataSet('logos_unit.xml');
    }

    public function testCheckObjectCreation(){
        $this->assertObjectHasAttribute('id', $this->user);
    }

}