<?php

namespace Logos\DB\Mongo;

use Logos\DB\AdapterAbstract;
use Logos\DB\Config;
use \MongoClient;

class Adapter extends AdapterAbstract{

    public function __construct(){

        $dsn = new MongoClient();

        $this->dbh = $dsn->{Config::read('db.name')};

    }

    public static function runQuery($query, $type, $tableName){

        $instance = self::getInstance();

        return $instance->dbh->{$tableName}->{$type}($query);

    }

}
