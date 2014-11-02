<?php


abstract class Logos_Mongo_Object extends Database_Object implements Database_Handler{

    public $id;

    public function __construct($id = null){
        $this->classDataSetup($id);
    }

    public function classDataSetup($id = null){

        if($id !== null){
            if(is_numeric($id)){
                $this->loadInto($id);
            }else{
                $this->updateObject(self::_dataToArray($id));
            }
        }
    }

    //-------------DB Object Creation

    //create new object in database with current given object
    public function createNew(){}

    //static version of createNew based on a given input of data, to be inserted into class
    public static function createSingle($data){}

    //Allows you to do a multiple object insert into a database.
    //Count field is optional, as count might depend on the number of rows in the array
    //Ideally, $data should be a numerically index array of associative arrays relating to each object being created
    //However, if you wish to create a number N of identical objects, data can be a single associative array instead
    public static function createMultiple($data, $count){}


    //-------------DB Object Update

    //Updates/saves changes to an object in the database, with an optional param $changedData
    public function save($changedData){}

    //Updates multiple objects with given data, and a conditional array
    public static function saveMultiple($changedData, $conditionArray){}


    //-------------DB Load Objects

    //Loads an object from a database into the container class
    public function loadInto($id){}

    //Loads a list of objects from the database with given conditions
    public function getList($conditionArray){}

    //Loads a single object from the database
    public static function load($conditionArray){}


    //-------------DB Delete Objects

    //Deletes/Removes/Erases a single object
    public function remove(){}

    //Deletes/Removes/Erases multiple objects based on a set of conditions
    public static function removeMultiple($conditionArray){}

    //Deletes/Removes/Erases an object based on an ID (can be an array)
    public static function destroy($id){}


    //-------------Object Related

    //gets the first object occurrence or creates a new one in the database
    public static function firstOrCreate($dataArray){}

    //gets the first object occurrence or returns a new instance of that object
    public static function firstOrNew($dataArray){}

    //returns an instance of an object with a given array of data
    public static function newInstance($dataArray){}

    public function updateObject($array){

        $keyChain = self::_getKeyChain();

        self::_dataToArray($array);

        foreach($array as $key => $value){
            if(array_key_exists($key, $keyChain))
                $this->{$key} = $value;
        }

        return $this;
    }

}

class Mongo_Core implements Database_Core{

    public $dbh;
    private static $instance;
    //Core is a singleton

    public function __construct(){

        $dsn = 'mysql:host=' . Config::read('db.host') .
            ';dbname='    . Config::read('db.base') .
            ';connect_timeout=15';

        //We use the @ symbol to supress errors because otherwise we would get the "mysql server has gone away"
        $this->dbh = @new PDO($dsn, Config::read('db.user'), Config::read('db.password'), array(PDO::ATTR_PERSISTENT => true));
        $this->dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); //PDO::ERRMODE_SILENT
        $this->dbh->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

        $this->query = new QueryHandler();

    }

    //Singleton get class
    public static function getInstance(){
        if (!isset(self::$instance)){
            $object = __CLASS__;
            self::$instance = new $object;
        }

        return self::$instance;
    }


}
