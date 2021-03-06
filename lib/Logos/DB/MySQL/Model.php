<?php

namespace Logos\DB\MySQL;

use Logos\DB\DatabaseObject;
use Logos\DB\HandlerInterface;
use Logos\Resources\Core;
use \Exception;
use \PDO;

//@TODO: create Schema syntax for object database creation
//@TODO: re-do querying to better follow php code conventions

abstract class Model extends DatabaseObject implements HandlerInterface{

    //-------------DB Object Creation

    /**
     * Create a new object in database with data based on current given object<br/><br/>
     *
     * 100 Queries Run
     * <p>Average Time: 48ms per 100/1.23kb</p>
     *
     * @return $this
     */
    public function createNew(){

        //A list of keys to be iterated though, generated by Object Attribute Names
        //We use the keychain because it gets non-dynamic property names,
        //which is what the database schema is based on.
        $keyChain = self::getKeyChain();
        $dataArray = [];

        $prepareStatement = "INSERT INTO `".self::name()."` (";

        foreach($keyChain as $key => $val){
            //since this is a new object, we don't want to save the ID, rather letting the DB generate an ID
            if($this->{$key} !== null && $key !== "id")
                $prepareStatement .= "$key, ";
            else
                unset($keyChain[$key]);

        }

        $prepareStatement = rtrim($prepareStatement, ", ").") VALUES (";

        //we are going to generate the array of variables to be processed by PDO
        //in example, color and count will be overwritten by PDO safely

        foreach($keyChain as $key => $val){
            $prepareStatement .= ":$key, ";

            $dataArray[':'.$key] = (mb_strpos($key,'date') !== false) ?
                Core::unixToMySQL($this->{$key}) : $this->{$key};
        }

        $prepareStatement = rtrim($prepareStatement, ", ").")";

        //at this point, the array should be good to go
        //INSERT INTO fruit (color, count) VALUES (:color, :count)

        //checks to see if there was an object that was inserted into the database
        $this->id = Adapter::fetchQuery($prepareStatement, $dataArray, false) ?
            Adapter::getInstance()->dbh->lastInsertId() : null;

        if($this->id === null)
            trigger_error("Error: object was not created in database");

        return $this;
    }

    /**
     * Static version of createNew, creates a single object in a databased with given $data<br/><br/>
     * <p>The difference between this and the calling create on a new instance of an object is that
     * createSingle has ability to not have to create an empty object, thus giving us a lower memory
     * count and a much lower execution time.</p>
     *
     * 100 Queries Run
     * <p>Average Time: 40ms per 100/0.375kb</p>
     * <br/>
     * self::newInstance($data)->createNew()
     * <p>Average Time: 49ms per 100/0.296kb</p>
     *
     * @param mixed $data
     * <p>Can be an array of matched object data, an object, or even a json string</p>
     *
     * @return mixed $data
     * <p>Returns either the data array plus the object ID or false if the object failed
     * to be inserted into the database</p>
     *
     * @throws exception if the data is not in a readable/convertible format
     */
    public static function createSingle($data){

        self::dataToArray($data);
        $keyChain = self::getKeyChain();

        $prepareStatement = "INSERT INTO `".self::name()."` (";

        foreach($data as $key => $val){
            //Here we check to see if the key meets our criteria. If it doesn't we want to unset the key so
            //don't have to sort through the $data array again, and make the same comparisons.
            if($val !== null && array_key_exists($key, $keyChain) && $keyChain[$key] !== "id")
                $prepareStatement .= "$key, ";
            else
                unset($data[$key]);
        }

        $prepareStatement = rtrim($prepareStatement, ", ").") VALUES (";

        foreach($data as $key => $val){
            $prepareStatement .= ":$key, ";
            //If an object has the word date in it, we want to convert it to a usable date mysql format
            $data[':'.$key] = (mb_strpos($key,'date') !== false) ?
                Core::unixToMySQL($val) : $val;
            //Un-setting the original key so there are no duplicates in the data array
            unset($data[$key]);
        }

        $prepareStatement = rtrim($prepareStatement, ", ").")";

        if(Adapter::fetchQuery($prepareStatement, $data, false))
            return Adapter::getInstance()->dbh->lastInsertId();

        return false;

    }


    /**
     * Used if you want to create multiple numbers of objects with a single query.
     * for any query greater then 1 object, its efficiency is about equal CreateSingle.
     * Every array in data should be set symmetric.
     *
     * 100 Queries Run
     * <p>Average Time: 6ms per 100/0.39kb (single Obj)</p>
     * <p>Average Time: 7ms per 100/0.28kb (arrays)</p>
     *
     * @param Array $data
     * <p>Can be an array of matched object data, an array of objects, or even an array of json strings.
     * It is recommended that all of the arrays inside $data be symmetrical.</p>
     *
     * @param mixed $count [optional]
     * <p>Can be an array of matched object data, an object, or even a json string</p>
     *
     * @return boolean
     * <p>Returns the result of query execute. If the execute was successful,
     * then returns true. False on fail</p>
     *
     * @trigger_error on incorrect $data format or if $data is given and not
     * an array of arrays, and count is not given.
     */
    public static function createMultiple($data, $count = null){

        $keyChain = self::getKeyChain();
        $goodKeys = $dataArray = [];

        $prepareStatement = "INSERT INTO `".self::name()."` (";

        //We can trigger an error here but we can try and treat it as a single query
        if($count === null && !isset($data[0]))
            $count = 1;

        //This creates a uniform data set to convert and set our remaining data to.
        if($count !== null && !isset($data[0]))
            $data = [$data];

        //We want to check that each array of data is usable in the builder, and not json or an object
        foreach($data as $objID => $obj){
            self::dataToArray($data[$objID]);
        }

        //Arrays inside $data should NOT be asymmetric.
        foreach(array_keys ($data[0]) as $value){
            if($value !== "id" && array_key_exists($value, $keyChain)){
                $goodKeys[$value] = true;
                $prepareStatement .= "$value, ";
            }
        }

        $prepareStatement = rtrim($prepareStatement, ", ").") VALUES ";

        //If the number of queries are based on the count of $data instead of smearing a single object
        if($count === null){
            foreach($data as $objID => $obj){
                $prepareStatement .= " (";

                foreach($goodKeys as $key => $val){
                    if(!isset($data[$objID][$key]))
                        $data[$objID][$key] = null;

                    if(array_key_exists($key, $goodKeys)){
                        $prepareStatement .= ":$key$objID, ";
                        $dataArray[':'.$key.$objID] = (mb_strpos($key,'date') !== false) ?
                            Core::unixToMySQL($data[$objID][$key]) : $data[$objID][$key];
                    }
                }

                $prepareStatement = rtrim($prepareStatement, ", ")."), ";
            }
        }else{
            $obj = $data[0];

            while($count > 0){
                $prepareStatement .= " (";

                foreach($obj as $key => $val){
                    if(array_key_exists($key, $goodKeys)){
                        $prepareStatement .= ":$key$count, ";
                        $dataArray[':'.$key.$count] = (mb_strpos($key,'date') !== false) ?
                            Core::unixToMySQL($val) : $val;
                    }
                }

                $prepareStatement = rtrim($prepareStatement, ", ")."), ";
                $count--;
            }
        }

        $prepareStatement = rtrim($prepareStatement, ", ");

        return Adapter::fetchQuery($prepareStatement, $dataArray, false);

    }

    //-------------DB Object Update

    /**
     * @TODO Should this throw exception on failure, or return false?
     * Updates/saves changes to an object in the database, with an optional param $changedData.
     * If changedData is null, then it will just use what data is in the class. If you however want to just change
     * a few things and already have an object, then use the changed data param
     *
     * 100 Queries Run
     * <p>Average Time: 40ms per 100/0.41kb</p>
     *
     * @param mixed $changedData [optional]
     * <p>An optional array/object/json string of data that is to be saved into the database relating to the
     * referenced object</p>
     *
     * @return $this
     * <p>Returns the result of query execute. If the execute was successful, then returns true. False on fail</p>
     *
     * @throws Exception
     * <p>If the ID of the object is not set. If the ID of the object isn't known, the use the static method</p>
     */
    public function save($changedData = null){

        $keyChain = self::getKeyChain();

        if($this->id === null)
            throw new Exception("Object has no ID, so cannot be saved using a non-static method.");

        if($changedData !== null)
            $this->updateObject($changedData);
        else
            $changedData = $this->toArray(true);

        $prepareStatement = "UPDATE `".self::name()."` SET ";
        self::_buildQuerySet($prepareStatement, $changedData, $keyChain);
        $prepareStatement .= " WHERE id = :id";

        $changedData["id"] = $this->id;

        foreach($changedData as $key => $val){
            $changedData[':'.$key] = (mb_strpos($key,'date') !== false) ? Core::unixToMySQL($val) : $val;
            unset($changedData[$key]);
        }

        //string should look like this:
        //UPDATE fruit SET color = :color, count = :count WHERE id = :id

        if(!Adapter::fetchQuery($prepareStatement, $changedData, false))
            trigger_error("Error: object was not saved");

        return $this;
    }

    /**
     * Saves a single object inside a DB.
     * Alias for saveMultiple
     *
     * @param $changedData
     * An array of data to be changed
     *
     * @param $conditionArray
     * Where the data is supposed to be changed
     *
     * @return bool
     */
    public static function saveSingle($changedData, $conditionArray){
        return self::saveMultiple($changedData, $conditionArray);
    }

    /**
     * Saves multiple objects inside a DB.
     *
     * 100 Queries Run
     * <p>Average Time: 1ms per 100/0.44kb</p>
     *
     * @param $changedData
     * An array of data to be changed
     *
     * @param $conditionArray
     * Where the data is supposed to be changed
     *
     * @return bool
     */

    public static function saveMultiple($changedData, $conditionArray){

        $keyChain = self::getKeyChain();

        self::dataToArray($changedData);
        self::dataToArray($conditionArray);

        $prepareStatement = "UPDATE `".self::name()."` SET ";

        self::_buildQuerySet($prepareStatement, $changedData, $keyChain);

        $prepareStatement .= " WHERE ";

        //This cannot be _buildWhereSet because we use two different data sets for the fetch query,
        //changedData and the conditionArray. We add the where clauses to the condition array
        //@TODO rewrite this so it uses the $conditionArray instead of $changedData, so we can use _buildQueryWhere

        foreach($conditionArray as $key => $value){
            if(array_key_exists($key, $keyChain)){
                $prepareStatement .= "{$key} = :w{$key} AND ";
                $changedData["w".$key] = $value;
            }
        }

        $prepareStatement = rtrim($prepareStatement, " AND ");

        foreach($changedData as $key => $val){
            $changedData[':'.$key] = (mb_strpos($key,'date') !== false) ?
                Core::unixToMySQL($val) : $val;

            unset($changedData[$key]);
        }

        //string should look like this:
        //UPDATE fruit SET color = :color, count = :count WHERE id = :id
        return Adapter::fetchQuery($prepareStatement, $changedData, false);
    }


    //-------------DB Load Objects

    /**
     * Loads data into an existing object
     *
     * 100 Queries Run
     * <p>Average Time: 39ms per 100/1.844kb</p>
     *
     * @param $id
     * ID of object to load, (this can also be an array of conditions)
     *
     * @return object|boolean
     */

    public function load($id){

        if(is_numeric($id)){

            Adapter::fetchQueryObj(
                "SELECT * FROM `".self::name()."` WHERE id = :id LIMIT 1",
                [":id" => $id],
                PDO::FETCH_INTO,
                $this
            );

        }else{

            self::dataToArray($id);

            $prepareStatement = "SELECT * FROM `".self::name()."` WHERE ";
            self::_buildQueryWhere($prepareStatement, $id);
            $prepareStatement .= " LIMIT 1";

            Adapter::fetchQueryObj($prepareStatement, $id, PDO::FETCH_INTO, $this);

        }

        return ($this->id !== null) ? $this : false;

    }

    /**
     * Gets a list of items from a Query
     *
     * 100 Queries Run
     * <p>Average Time: 4ms per 100/0.422kb</p>
     *
     * @param null $conditionArray [optional]
     * Matching conditions for the list
     *
     * @return Array
     */

    public function getList($conditionArray = null){

        self::dataToArray($conditionArray);
        $name = self::name();

        $prepareStatement = "SELECT * FROM `".$name."`";

        if($conditionArray !== null){
            $prepareStatement .= " WHERE ";
            self::_buildQueryWhere($prepareStatement, $conditionArray);
        }

        return Adapter::fetchQueryObj($prepareStatement, $conditionArray, PDO::FETCH_CLASS, $name);

    }

    /**
     * Loads a single object from the database
     *
     * 100 Queries Run
     * <p>Average Time: 47ms per 100/0.355kb</p>
     *
     * @param $conditionArray
     * Matching Conditions for the object to be loaded
     *
     * @return boolean|array|object
     */

    public static function loadSingle($conditionArray){

        self::dataToArray($conditionArray);
        $name = self::name();

        $prepareStatement = "SELECT * FROM `".$name."` WHERE ";

        if(!is_array($conditionArray))
            $conditionArray = is_numeric($conditionArray) ? ["id" => $conditionArray] : false;

        self::_buildQueryWhere($prepareStatement, $conditionArray);

        $prepareStatement .= " LIMIT 1";

        return Adapter::fetchQueryObj($prepareStatement, $conditionArray, PDO::FETCH_OBJ, $name);

    }

    /**
     * Loads Multiple Objects from the database
     *
     * 100 Queries Run
     * <p>Average Time: 4ms per 100/0.422kb</p>
     *
     * @param null $conditionArray [optional]
     * Matching conditions for the list
     *
     * @return Array
     */

    //Static version of getList
    public static function loadMultiple($conditionArray = null){

        self::dataToArray($conditionArray);
        $name = self::name();

        $prepareStatement = "SELECT * FROM `".$name."`";

        if($conditionArray !== null){
            $prepareStatement .= " WHERE ";
            self::_buildQueryWhere($prepareStatement, $conditionArray);
        }

        return Adapter::fetchQueryObj($prepareStatement, $conditionArray, PDO::FETCH_CLASS, $name);

    }

    //-------------DB Delete Objects

    /**
     * Removes a object, non-static alias for destroy($id)
     *
     * 100 Queries Run
     * <p>Average Time: 38ms per 100/0.325kb</p>
     *
     * @return bool
     */

    public function remove(){
        return self::destroy($this->id);
    }

    /**
     * Removes multiple objects based on a condition array
     *
     * 100 Queries Run
     * <p>Average Time: 2ms per 100/1.265kb</p>
     *
     * @param $conditionArray
     *
     * @return bool
     */

    public static function removeMultiple($conditionArray){

        $prepareStatement = "DELETE FROM `".self::name()."` WHERE ";

        self::_buildQueryWhere($prepareStatement, $conditionArray);

        return Adapter::fetchQuery($prepareStatement, $conditionArray);

    }

    /**
     * Statically removes an object with a given ID from a database
     *
     * 100 Queries Run
     * <p>Average Time: 38ms per 100/0.325kb</p>
     *
     * @param $id
     * ID of object to be removed
     *
     * @return bool
     */

    public static function destroy($id){
        return Adapter::fetchQuery(
            "DELETE FROM ".self::name()." WHERE id = :id",
            [':id' => $id]
        );
    }


    //-------------Object Related

    /**
     * Gets the first object occurrence of an object in the database, or creates a new object
     * if one doesn't exist
     *
     * @param $dataArray
     *
     * @return $this
     */
    public static function firstOrCreate($dataArray){

        $obj = self::firstOrNew($dataArray);

        return !is_numeric($obj->id) ? $obj->createNew() : $obj;

    }

    /**
     * Gets the first object occurrence or returns a new instance of that object
     *
     * @param $dataArray
     *
     * @return $this
     */
    public static function firstOrNew($dataArray){

        $obj = self::loadSingle($dataArray);

        return !is_object($obj) ? self::newInstance($dataArray) : $obj;

    }

    /**
     * Adds to a mysql query for grouping/ordering/limiting results
     *
     * @param $functionCall
     * What is added (orderBy, limit, groupBy) can be array or string
     *
     * @param null $params [optional]
     * The parameters of the query ('10', 'id ADC') can be array or string
     *
     * @return $this - returns new instance of self
     *
     * Examples:
     * Object::query('limit', 10)->getList();
     * Object::query(['orderBy', 'limit'], ['id DESC', 10])->getList();
     * Object::query(['orderBy', 'limit'], ['id ASC', 10])->getList();
     * Object::query(['orderBy' => 'id ASC', 'limit' => 10])->getList();
     *
     * Min/Max Limiting
     * Object::query('limit', [0, 10])->getList();
     * Object::query('limit', ['min' => 0, 'max' => 10])->getList();
     *
     * Or if you want to use an array,
     * Object::query(['limit' => [0, 10]])->getList();
     * Object::query(['limit' => ['min' => 0, 'max' => 10]])->getList();
     */

    public static function query($functionCall, $params = null){

        $callable = new QueryHandler();

        if($params === null){
            if(!is_array($functionCall))
                trigger_error("Invalid Use of Query. No Params Given, and invalid Key/Value pairs");

            foreach($functionCall as $key => $value){
                $tempKey = strtolower($key);

                if(is_callable([$callable, $tempKey], true))
                    Adapter::getInstance()->query->$tempKey($value);

            }

        }else{
            if(!is_array($functionCall)){
                $functionCall = strtolower($functionCall);

                if(is_callable([$callable, $functionCall], true))
                    Adapter::getInstance()->query->$functionCall($params);

            }else{
                foreach($functionCall as $key => $value){
                    $value = strtolower($value);

                    if(is_callable([$callable, $value], true))
                        Adapter::getInstance()->query->$value($params[$key]);

                }
            }
        }

        return self::newInstance();
    }

    /**
     * Builds a PDO query for MYSQL Update. ex: SET thing = :thing, thing2 = :thing2
     *
     * @param $prepareStatement
     *
     * @param $conditionArray
     *
     * @param null $keyChain [optional]
     *
     * @return void
     */

    private static function _buildQuerySet(&$prepareStatement, &$conditionArray, &$keyChain = []){

        if(count($keyChain) === 0)
            $keyChain = self::getKeyChain();

        foreach($conditionArray as $key => $val){
            if($val !== null && $key !== "id" && array_key_exists($key, $keyChain))
                $prepareStatement .= "$key = :$key, ";
            else
                unset($conditionArray[$key]);
        }

        $prepareStatement = rtrim($prepareStatement, ", ");
    }

    /**
     * Builds a PDO query for MYSQL WHERE. ex: WHERE thing = :thing AND thing2 = :thing2
     *
     * @param $prepareStatement
     *
     * @param $conditionArray
     *
     * @param null $keyChain [optional]
     *
     * @return void
     */

    private static function _buildQueryWhere(&$prepareStatement, &$conditionArray, &$keyChain = null){

        if($keyChain === null)
            $keyChain = self::getKeyChain();

        foreach($conditionArray as $key => $value){
            if(array_key_exists($key, $keyChain)){
                $prepareStatement .= "{$key} = :{$key} AND ";
                $conditionArray[":".$key] = $value;
                unset($conditionArray[$key]);
            }
        }

        $prepareStatement = rtrim($prepareStatement, "AND ");
    }
}