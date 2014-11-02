<?php

abstract class DatabaseObject{

    protected static function _name(){

        $className = explode("\\", get_called_class());

        return end($className);

    }

    //returns an instance of an object with a given array of data
    public static function newInstance($dataArray = null){

        $className = get_called_class();

        return new $className($dataArray);

    }

    public function toArray(){

        return get_object_vars($this);

    }

    protected static function _getKeyChain(){

        return get_class_vars(get_called_class());

    }

    protected static function _dataToArray(&$dataToFilter){

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

        $keyChain = self::_getKeyChain();

        self::_dataToArray($array);

        foreach($array as $key => $value){
            if(array_key_exists($key, $keyChain))
                $this->{$key} = $value;
        }

        return $this;
    }
}