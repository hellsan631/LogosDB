<?php

/**
 * Class MySQL_Adapter
 *
 * Handles all database interaction for the object class.
 */
class MySQL_Adapter extends Database_Adapter{

    public $dbh;
    public $query;

    public function __construct(){

        $dsn = 'mysql:host=' . Config::read('db.host') .
            ';dbname='    . Config::read('db.name') .
            ';connect_timeout=15';

        //We use the @ symbol to suppress errors because otherwise we would get the "mysql server has gone away"
        $this->dbh = @new PDO($dsn,
            Config::read('db.user'),
            Config::read('db.password'),
            array(PDO::ATTR_PERSISTENT => true)
        );

        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO::ERRMODE_SILENT
        $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);

        $this->query = new QueryHandler();

    }

    public static function fetchQuery($prepare, $execute, $returnQuery = true){

        try {

            $newInstance = self::getInstance();

            $query = $newInstance->dbh->prepare($prepare.$newInstance->query->getQuery());

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

    public static function fetchQueryObj($prepare, $execute, $fetchMode, &$fetchParam){

        try {

            $newInstance = self::getInstance();

            $query = $newInstance->dbh->prepare($prepare.$newInstance->query->getQuery());

            if($fetchMode === PDO::FETCH_OBJ)
                $query->setFetchMode($fetchMode);
            else
                $query->setFetchMode($fetchMode, $fetchParam);

            $query->execute($execute);

            if($fetchMode === PDO::FETCH_OBJ){

                $fetchParam = $query->fetchObject($fetchParam);

                if(!is_object($fetchParam) && (!is_array($fetchParam) or count($fetchParam) == 0))
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

/**
 * Class MySQL_Core
 *
 * Class to maintain 1.4.* backwards compatibility. Will be removed in 1.5.*
 *
 * @deprecated
 */
class MySQL_Core extends MySQL_Adapter{

}