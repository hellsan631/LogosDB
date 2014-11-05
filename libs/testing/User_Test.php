<?php

abstract class User_Test extends Generic_DB_Test{

    public static $db_table_name;

    public function getDataSet(){
        return $this->createXMLDataSet('logos_unit.xml');
    }

    public function testCheckObjectCreation(){
        $this->assertObjectHasAttribute('id', $this->user);
    }

    public function testDatabaseRowInitCount(){
        $this->assertEquals($this->TableCount, $this->getConnection()->getRowCount(self::$db_table_name), "Pre-Condition");
    }

    //------------Test Object Creation

    public function testDatabaseCreateNewObject(){

        $object = self::$db_table_name;

        $this->user = new $object(['username' => 'HellsAn631', 'email'=> 'hellsan631@email.com']);
        $this->assertTrue($this->user->id === null, "User has no ID when not in database");
        $this->assertTrue(is_object($this->user->createNew()), "Object returned on DB creation");
        $this->assertEquals(($this->TableCount+1), $this->getConnection()->getRowCount(self::$db_table_name), "User Created In Database");
        $this->assertTrue($this->user->id !== null, "User ID loaded properly");

    }

    public function testDatabaseCreateSingleObject(){

        $object = self::$db_table_name;

        $this->user->id = $object::createSingle(['username' => 'HellsAn631', 'email'=> 'hellsan631@email.com']);
        $this->assertTrue($this->user->id !== null && $this->user->id !== false, "User ID was gotten from creation");
        $this->assertEquals(($this->TableCount+1), $this->getConnection()->getRowCount(self::$db_table_name), "User Created In Database");

    }

    public function testDatabaseCreateMultipleObjects(){

        $object = self::$db_table_name;

        $objects = [
            ['username' => 'HellsAn6', 'email'=> 'hellsan631@email.com'],
            ['username' => 'HellsAn3', 'email'=> 'hellsan631@email.com'],
            ['username' => 'HellsAn1', 'email'=> 'hellsan631@email.com']
        ];

        $this->users = $object::createMultiple($objects);
        $this->assertTrue($this->users !== false, "Create Multiple Worked and returned correctly");
        $this->assertEquals(($this->TableCount+3), $this->getConnection()->getRowCount(self::$db_table_name), "Users were created in database");
    }

    //------------Test Object Loading

    public function testDatabaseCanLoadObject(){
        $object = self::$db_table_name;

        $this->user = $object::load(['username' => 'HellsAn631']);
        $this->assertTrue($this->user->id !== null);
    }

    public function testDatabaseCanLoadIntoObject(){

        $object = self::$db_table_name;

        $this->user = new $object();
        $this->user->loadInto(1);
        $this->assertTrue($this->user->id !== null);

        $this->user = new $object(1);
        $this->assertTrue($this->user->id !== null);

    }

    public function testDatabaseCanLoadMultipleObjects(){

        $object = self::$db_table_name;

        $list = $object::newInstance()->getList(['email' => 'hellsan631@email.com']);

        $this->assertTrue(count($list) > 0);
        $this->assertTrue(is_array($list));

        $list = $object::loadMultiple(['email' => 'hellsan631@email.com']);

        $this->assertTrue(count($list) > 0);
        $this->assertTrue(is_array($list));

    }

    //------------Test Object Load or New

    public function testDatabaseCanLoadFirstOrReturnNewInstanceOfObject(){

    }

    public function testDatabaseCanLoadFirstOrCreateObject(){

    }

    //------------Test Object Saving

    public function testDatabaseCanSaveObject(){

    }

    public function testDatabaseCanSaveMultipleObjects(){

    }

    //------------Test Object Removal

    public function testDatabaseCanRemoveObject(){

    }

    public function testDatabaseCanDeleteSingleObject(){

    }

    public function testDatabaseCanDeleteMultipleObjects(){

    }

    //------------Test Object Misc Functions

    public function testCanReturnJsonObject(){
        $object = self::$db_table_name;

        $json = json_encode(['id' => 1, 'username' => 'HellsAn631', 'email'=> 'hellsan631@email.com']);
        $this->user = new $object($json);
        $this->assertJsonStringEqualsJsonString($json,$this->user->toJson());
    }


}