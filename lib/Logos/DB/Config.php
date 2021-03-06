<?php

namespace Logos\DB;

class Config{

    private static $confArray;

    public static function read($name){
        return self::$confArray[$name];
    }

    public static function write($name, $value){
        self::$confArray[$name] = $value;
    }

}