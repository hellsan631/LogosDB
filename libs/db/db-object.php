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
            if(is_numeric($id))
                $this->load($id);
            else
                $this->updateObject($id);
        }
    }

    abstract public function load($id);

    /**
     * Ensures that data (JSON String, Objects) are turned into arrays for processing
     * @param $dataToFilter
     * @return array
     */

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

    /**
     * @param $dataToUpdate
     * The data (JSON, array, or object) which you want added to the object
     *
     * @param bool $adhere
     * Adhere's input data to the model. If you want to store data that is outside the model,
     * then you can set this to false.
     *
     * @return $this
     */

    public function updateObject(&$dataToUpdate, $adhere = true){
        $keyChain = self::getKeyChain();

        self::dataToArray($dataToUpdate);

        foreach($dataToUpdate as $key => $value){
            if($adhere === false)
                $this->{$key} = $value;
            else if(array_key_exists($key, $keyChain))
                $this->{$key} = $value;
        }

        return $this;
    }

    //returns an instance of an object with a given array of data
    public static function newInstance($dataArray = null){
        $className = self::name();

        return new $className($dataArray);
    }

    /**
     * Returns an array of the object's internal data
     *
     * @param bool $emptyNull
     * If $emptyNull is set to true, toArray will return an array of values that are NOT null.
     * Default behavior is set to always return all values, even if they are null
     *
     * @return array
     */
    public function toArray($emptyNull = false){

        if($emptyNull){
            $array = get_object_vars($this);

            foreach($array as $key => &$value){
                if($value === null)
                    unset($array[$key]);
            }

            return $array;
        }

        return get_object_vars($this);
    }

    /**
     * Returns a JSON string of an object, with no empty values
     * @return string
     */
    public function toJson(){
        return json_encode($this->toArray(true));
    }

    /**
     * Gets an array of the classes namee variables
     * @return array
     */
    protected static function getKeyChain(){
        return get_class_vars(get_called_class());
    }


    /**
     * Gets the name of the class
     * @return string
     */
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