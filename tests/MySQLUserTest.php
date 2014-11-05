<?php

class MySQLUserTest extends User_Test{

    public function setUp(){

        self::$db_table_name = "MySQL_User";

        $object = self::$db_table_name;

        Config::write('db.host', 'localhost');
        Config::write('db.name', 'logos_unit');
        Config::write('db.user', 'logos_user');
        Config::write('db.password', 'vYZ9J2aRTHveMhQZ');

        $this->user = new $object();

        $this->TableCount = $this->getConnection()->getRowCount(self::$db_table_name);
    }

}