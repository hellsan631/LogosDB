<?php

abstract class User_Test extends Generic_DB_Test{

    public function testCheckObjectCreation(){
        $this->assertObjectHasAttribute('id', $this->user);
    }

    public function testDatabaseRowInitCount(){
        $this->assertEquals(
            $this->TableCount,
            $this->getConnection()->getRowCount(self::$db_table_name),
            "Pre-Condition"
        );
    }

    //------------Test Object Creation

    public function testDatabaseCreateNewObject(){

        $object = self::$db_table_name;

        $this->user = new $object(['username' => 'HellsAn631', 'email'=> 'hellsan631@email.com']);
        $this->assertTrue($this->user->id === null, "User has no ID when not in database");

        $this->assertTrue(
            is_object($this->user->createNew()),
            "Object returned on DB creation"
        );

        $this->assertEquals(
            ($this->TableCount+1),
            $this->getConnection()->getRowCount(self::$db_table_name),
            "User Created In Database"
        );

        $this->assertTrue($this->user->id !== null, "User ID loaded properly");

    }

    public function testDatabaseCreateSingleObject(){

        $object = self::$db_table_name;

        $this->user->id = $object::createSingle([
            'username' => 'HellsAn631',
            'email'=> 'hellsan631@email.com'
        ]);

        $this->assertTrue(
            $this->user->id !== null && $this->user->id !== false,
            "User ID was gotten from creation"
        );

        $this->assertEquals(
            $this->TableCount+1,
            $this->getConnection()->getRowCount(self::$db_table_name),
            "User Created In Database"
        );

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

        $this->assertEquals(
            $this->TableCount+3,
            $this->getConnection()->getRowCount(self::$db_table_name),
            "Users were created in database"
        );
    }

    //------------Test Object Loading

    public function testDatabaseCanLoadObject(){
        $object = self::$db_table_name;

        $this->user = $object::loadSingle(['username' => 'HellsAn631']);
        $this->assertTrue($this->user->id !== null);
    }

    public function testDatabaseCanLoadIntoObject(){

        $object = self::$db_table_name;

        $savedID = $object::createSingle(['username' => 'HellsAn631', 'email'=> 'hellsan631@email.com']);

        $this->assertEquals(
            $this->TableCount+1,
            $this->getConnection()->getRowCount(self::$db_table_name),
            "User Created In Database"
        );

        $this->user = new $object();
        $this->user->load($savedID);
        $this->assertTrue($this->user->id !== null);

        $this->user = new $object($savedID);
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

        $object = self::$db_table_name;

        $max = 10000;
        $random = mt_rand(0,$max*$max);

        $this->user = $object::firstOrNew(['username' => "$random"]);
        $this->assertTrue($this->user->id === null);

        $this->user = $object::firstOrNew(['email' => 'hellsan631@email.com']);
        $this->assertTrue($this->user->id !== null);

    }

    public function testDatabaseCanLoadFirstOrCreateObject(){

        $object = self::$db_table_name;

        $max = 10000;
        $random = mt_rand(0,$max*$max);

        $this->user = $object::firstOrCreate(['username' => "$random"]);
        $this->assertTrue($this->user->id !== null);

        $this->user = $object::loadSingle(['username' => $random]);
        $this->assertTrue($this->user->id !== null);

    }

    //------------Test Object Saving

    public function testDatabaseCanSaveObject(){

        $object = self::$db_table_name;

        $max = 10000;
        $random = mt_rand(0,$max*$max);

        $this->user = $object::loadSingle(['email' => 'hellsan631@email.com']);
        $this->assertTrue($this->user->id !== null);

        $this->assertTrue(
            $this->user->save(['email' => "$random@email.com"])->email !== 'hellsan631@email.com',
            'Check to see that save returns the updated user'
        );

        $this->assertTrue($this->user->email !== 'hellsan631@email.com');

        $this->user = $object::loadSingle(['email' => "$random@email.com"]);
        $this->assertTrue($this->user->id !== null);

    }

    public function testDatabaseCanSaveMultipleObjects(){

        $object = self::$db_table_name;

        $max = 10000;
        $random =  mt_rand(0,$max*$max);

        $list = $object::newInstance()->getList(['email' => 'hellsan631@email.com']);
        $temp = [];

        $this->assertTrue(count($list) > 0);
        $this->assertTrue(is_array($list));

        $count = 3;

        foreach($list as $value){
            array_push($temp, $value);
            $count--;

            if($count <= 0)
                break;
        }

        foreach($temp as $value){
            $object::saveMultiple(["username" => $random], ["id" =>  $value->id]);
        }

        $list = $object::newInstance()->getList(['username' => $random]);

        $this->assertTrue(count($list) > 0);
        $this->assertTrue(is_array($list));

    }

    //------------Test Object Removal

    public function testDatabaseCanRemoveObject(){

        $object = self::$db_table_name;

        $this->user = $object::loadSingle(['username' => 'HellsAn631']);
        $this->assertTrue($this->user->id !== null);

        $savedID = $this->user->id;

        $this->user = new $object($savedID);
        $this->assertTrue($this->user->id !== null, "There exists an object to be deleted");

        $this->user->remove();

        $list = $object::newInstance()->getList(['id' => $savedID]);

        $this->assertTrue(count($list) === 0, "Number of objects in db that have the deleted ID are 0");
        $this->assertTrue(is_array($list));

    }

    public function testDatabaseCanDeleteSingleObject(){

        $object = self::$db_table_name;

        $this->user = $object::loadSingle(['username' => 'HellsAn631']);
        $this->assertTrue($this->user->id !== null);

        $savedID = $this->user->id;

        $this->user = new $object($savedID);
        $this->assertTrue($this->user->id !== null, "There exists an object to be deleted");

        $object::destroy($savedID);

        $list = $object::newInstance()->getList(['id' => $savedID]);

        $this->assertTrue(count($list) === 0, "Number of objects in db that have the deleted ID are 0");
        $this->assertTrue(is_array($list));

    }

    public function testDatabaseCanDeleteMultipleObjects(){

        $object = self::$db_table_name;

        $objects = [
            ['username' => 'HellsAn6', 'email'=> 'todelete@email.com'],
            ['username' => 'HellsAn3', 'email'=> 'todelete@email.com'],
            ['username' => 'HellsAn1', 'email'=> 'todelete@email.com']
        ];

        $this->users = $object::createMultiple($objects);
        $this->assertTrue($this->users !== false, "Create Multiple Worked and returned correctly");
        $this->assertEquals(
            $this->TableCount+3,
            $this->getConnection()->getRowCount(self::$db_table_name),
            "Users were created in database"
        );

        $object::removeMultiple(['email'=> 'todelete@email.com']);

        $this->assertEquals(
            $this->TableCount,
            $this->getConnection()->getRowCount(self::$db_table_name),
            "Users were deleted from database"
        );

    }

    //------------Test Object Misc Functions

    public function testCanReturnJsonObject(){
        $object = self::$db_table_name;

        $json = json_encode(['id' => 1, 'username' => 'HellsAn631', 'email'=> 'hellsan631@email.com']);
        $this->user = new $object($json);
        $this->assertJsonStringEqualsJsonString($json,$this->user->toJson());
    }


}