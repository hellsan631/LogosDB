<?php

namespace Logos\DB;

/**
 * Class Database_Adapter
 *
 * Outlines that all DatabaseAdapters are singletons
 */
abstract class AdapterAbstract{

    public $dbh;
    private static $instance;
    //Core is a singleton

    //Ensures that anything that implements a Database_Core is a singleton
    public static function getInstance(){
        if (!isset(self::$instance)){
            $object = get_called_class();
            self::$instance = new $object;
        }

        return self::$instance;
    }

}