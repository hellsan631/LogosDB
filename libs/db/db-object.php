<?php

abstract class Database_Object{

    public $id;

    public function __construct($id = null){
        $this->classDataSetup($id);
    }

    /**
     * Handles the object loading from the database when an ID is passed
     * @param mixed $id [optional]
     * <p>Can be an array of matched object data, the object ID, an object, or even a json string</p>
     * @return void
     */

    public function classDataSetup($id = null){

        if($id !== null){
            if(is_numeric($id)){
                $this->loadInto($id);
            }else{
                $this->updateObject(self::dataToArray($id));
            }
        }
    }

    abstract public function loadInto($id);

    abstract public function getList($conditionArray);

    //Static version of getList
    public static function loadMultiple($conditionArray = null){
        return self::newInstance()->getList($conditionArray);
    }

    protected static function dataToArray(&$dataToFilter){
        if(!is_array($dataToFilter)){
            if(is_object($dataToFilter))
                $dataToFilter = (array) $dataToFilter;
            else if(Core::isJson($dataToFilter))
                $dataToFilter = json_decode($dataToFilter, true);
            else
                trigger_error("Tried to filter Malformed data");
        }

        return $dataToFilter;
    }

    public function updateObject($array){
        $keyChain = self::getKeyChain();

        self::dataToArray($array);

        foreach($array as $key => $value){
            if(array_key_exists($key, $keyChain))
                $this->{$key} = $value;
        }

        return $this;
    }

    //returns an instance of an object with a given array of data
    public static function newInstance($dataArray = null){
        $className = self::name();

        return new $className($dataArray);
    }

    public function toArray(){
        return get_object_vars($this);
    }

    public function toJson(){
        return json_encode($this->toArray());
    }

    protected static function getKeyChain(){
        return get_class_vars(get_called_class());
    }

    protected static function name(){

        $className = explode("\\", get_called_class());

        return end($className);
    }

    //Magic Methods
    //serialize
    public function __sleep(){
        $keys = self::getKeyChain();

        $temp = [];

        foreach($keys as $key => $value){
            if($this->{$key} !== null)
                array_push($temp, $key);
        }

        return $temp;
    }

    public function __toString(){
        return json_encode($this->toArray(), JSON_FORCE_OBJECT);
    }

    public function __invoke($dataArray){
        $object = self::name();

        return new $object($dataArray);
    }
}