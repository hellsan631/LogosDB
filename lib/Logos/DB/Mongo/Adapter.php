<?php

class Mongo_Adapter extends Database_Adapter{

    public function __construct(){

        $dsn = new MongoClient();

        $this->dbh = $dsn->{Config::read('db.name')};

    }

    public static function runQuery($query, $type, $tableName){

        $instance = self::getInstance();

        return $instance->dbh->{$tableName}->{$type}($query);

    }

}
