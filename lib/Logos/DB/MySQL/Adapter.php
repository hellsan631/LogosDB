<?php

namespace Logos\DB\MySQL;

use Logos\DB\Database_Adapter;
use Logos\DB\Config;
use \PDO;
use \PDOException;
use \PDOStatement;

/**
 * Class MySQL_Adapter
 *
 * Handles all database interaction for the object class.
 */
class MySQL_Adapter extends Database_Adapter{

    public $dbh;
    public $query;

    public function __construct(){
        $this->connect();
    }

    /**
     * This method is used to connect to the current config database
     *
     * @return $this
     */
    public function connect(){

        $dsn = 'mysql:host=' . Config::read('db.host') .
            ';dbname='    . Config::read('db.name') .
            ';connect_timeout=15';

        //We use the @ symbol to suppress "mysql server has gone away" errors
        $this->dbh = @new PDO($dsn,
            Config::read('db.user'),
            Config::read('db.password'),
            [PDO::ATTR_PERSISTENT => true]
        );

        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO::ERRMODE_SILENT
        $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        $this->query = new QueryHandler();

        return $this;
    }

    /**
     * Runs a simple PDO query/execute which can return the query itself or the results of the query
     *
     * @param $prepare
     * @param $execute
     *
     * @param bool $returnQuery [optional]
     *
     * @return bool|array|PDOStatement
     */
    public static function fetchQuery($prepare, $execute, $returnQuery = true){

        try {

            $newInstance = self::getInstance();

            $query = $newInstance->dbh->prepare($newInstance->query->getQuery($prepare));

            if(!$returnQuery)
                return $query->execute($execute);
            else
                $query->execute($execute);

            return $query;

        }catch(PDOException $pe) {
            trigger_error('Could not connect to MySQL database. ' . $pe->getMessage() , E_USER_ERROR);
        }

        return false;
    }

    /**
     * Runs a PDO Query with a specified fetch mode, which is meant to return an object or class
     * or perform a FETCH_INTO an existing object.
     *
     * @param $prepare
     * @param $execute
     *
     * @param $fetchMode
     * @param $fetchParam
     *
     * @return bool|array|object
     */
    public static function fetchQueryObj($prepare, $execute, $fetchMode, &$fetchParam){

        try {
            $newInstance = self::getInstance();

            $query = $newInstance->dbh->prepare($newInstance->query->getQuery($prepare));

            //if the fetch mode is object then we won't need a fetch param
            if($fetchMode === PDO::FETCH_OBJ)
                $query->setFetchMode($fetchMode);
            else
                $query->setFetchMode($fetchMode, $fetchParam);

            $query->execute($execute);

            if($fetchMode === PDO::FETCH_OBJ){

                $fetchParam = $query->fetchObject($fetchParam);

                if(!is_object($fetchParam) and (!is_array($fetchParam) or count($fetchParam) == 0))
                    return false;

            }else if($fetchMode === PDO::FETCH_INTO)
                $query->fetch();
            else if($fetchMode === PDO::FETCH_CLASS)
                return $query->fetchAll($fetchMode, $fetchParam);

            return $fetchParam;

        }catch(PDOException $pe) {
            trigger_error('Error fetching Object. ' . $pe->getMessage() , E_USER_ERROR);
        }

        return false;
    }
}