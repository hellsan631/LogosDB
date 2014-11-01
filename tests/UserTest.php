<?php

    class UserTest extends PHPUnit_Extensions_Database_TestCase{

        public function setUp(){

            Config::write('db.host', 'localhost');
            Config::write('db.base', 'logos_unit');
            Config::write('db.user', 'logos_user');
            Config::write('db.password', 'vYZ9J2aRTHveMhQZ');

            $this->User = new User();

        }

    }