<?php

//@TODO: create Schema syntax for object database creation
//@TODO: commenting
//@TODO: re-do querying to better follow php code conventions

abstract class Logos_MySQL_Object extends Database_Object implements Database_Handler{

    //-------------DB Object Creation

    /**
     * Create a new object in database with data based on current given object<br/><br/>
     *
     * 100 Queries Run
     * <p>Average Time: 48ms per 100/1.23kb</p>
     *
     * @return Object $this
     */
    public function createNew(){

        //A list of keys to be iterated though, generated by Object Attribute Names
        //We use the keychain because it gets non-dynamic property names, which is what the database schema is based on.
        $keyChain = self::getKeyChain();
        $dataArray = [];

        $prepareStatement = "INSERT INTO ".self::name()." (";

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
        $this->id = MySQL_Core::fetchQuery($prepareStatement, $dataArray, false) ?
            MySQL_Core::getInstance()->dbh->lastInsertId() : null;


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

        $prepareStatement = "INSERT INTO ".self::name()." (";

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

        if(MySQL_Core::fetchQuery($prepareStatement, $data, false))
            return MySQL_Core::getInstance()->dbh->lastInsertId();

        return false;

    }


    /**
     * Used if you want to create multiple numbers of objects with a single query. for any query greater then 1 object,
     * its efficiency is about equal CreateSingle. Every array in data should be set symmetric.
     *
     * 100 Queries Run
     * <p>Average Time: 6ms per 100/0.39kb (single Obj)</p>
     * <p>Average Time: 7ms per 100/0.28kb (arrays)</p>
     *
     * @param Array $data
     * <p>Can be an array of matched object data, an array of objects, or even an array of json strings.
     * It is recommended that all of the arrays inside $data be symmetrical.</p>
     *
     * @param mixed $count
     * <p>Can be an array of matched object data, an object, or even a json string</p>
     *
     * @return boolean
     * <p>Returns the result of query execute. If the execute was successful, then returns true. False on fail</p>
     *
     * @trigger_error on incorrect $data format or if $data is given and not an array of arrays, and count is not given.
     */
    public static function createMultiple($data, $count = null){

        $keyChain = self::getKeyChain();
        $goodKeys = $dataArray = [];

        $prepareStatement = "INSERT INTO ".self::name()." (";

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

        return MySQL_Core::fetchQuery($prepareStatement, $dataArray, false);

    }

    //-------------DB Object Update

    /**
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
     * @return boolean
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

        $prepareStatement = "UPDATE ".self::name()." SET ";
        self::_buildQuerySet($prepareStatement, $changedData, $keyChain);
        $prepareStatement .= " WHERE id = :id";

        $changedData["id"] = $this->id;

        foreach($changedData as $key => $val){
            $changedData[':'.$key] = (mb_strpos($key,'date') !== false) ? Core::unixToMySQL($val) : $val;
            unset($changedData[$key]);
        }

        //string should look like this:
        //UPDATE fruit SET color = :color, count = :count WHERE id = :id

        if(!MySQL_Core::fetchQuery($prepareStatement, $changedData, false))
            throw new Exception("Object couldn't be saved");

        return $this;
    }

    /**
    * 100 Queries Run
    * <p>Average Time: 1ms per 100/0.44kb</p>
    */

    //alias for saveMultiple
    public static function saveSingle($changedData, $conditionArray){
        return self::saveMultiple($changedData, $conditionArray);
    }

    public static function saveMultiple($changedData, $conditionArray){

        $keyChain = self::getKeyChain();

        self::dataToArray($changedData);
        self::dataToArray($conditionArray);

        $prepareStatement = "UPDATE ".self::name()." SET ";

        self::_buildQuerySet($prepareStatement, $changedData, $keyChain);

        $prepareStatement .= " WHERE ";

        foreach($conditionArray as $key => $value){
            if(array_key_exists($key, $keyChain)){
                $prepareStatement .= "{$key} = :w{$key}, ";
                $changedData["w".$key] = $value;
            }
        }

        $prepareStatement = rtrim($prepareStatement, ", ");

        foreach($changedData as $key => $val){
            $changedData[':'.$key] = (mb_strpos($key,'date') !== false) ?
                Core::unixToMySQL($val) : $val;

            unset($changedData[$key]);
        }

        //string should look like this:
        //UPDATE fruit SET color = :color, count = :count WHERE id = :id

        return MySQL_Core::fetchQuery($prepareStatement, $changedData, false);

    }


    //-------------DB Load Objects

    /**
     * 100 Queries Run
     * <p>Average Time: 39ms per 100/1.844kb</p>
     */

    public function load($id){

        return MySQL_Core::fetchQueryObj(
                "SELECT * FROM ".self::name()." WHERE id = :id LIMIT 1",
                [":id" => $id],
                PDO::FETCH_INTO,
                $this
            );

    }

    /**
     * 100 Queries Run
     * <p>Average Time: 4ms per 100/0.422kb</p>
     */

    public function getList($conditionArray = null){

        self::dataToArray($conditionArray);
        $name = self::name();

        $prepareStatement = "SELECT * FROM ".$name;

        if($conditionArray !== null){
            $prepareStatement .= " WHERE ";
            self::_buildQueryWhere($prepareStatement, $conditionArray);
        }

        return MySQL_Core::fetchQueryObj($prepareStatement, $conditionArray, PDO::FETCH_CLASS, $name);

    }

    /**
     * 100 Queries Run
     * <p>Average Time: 47ms per 100/0.355kb</p>
     */

    public static function loadSingle($conditionArray){

        self::dataToArray($conditionArray);
        $name = self::name();

        $prepareStatement = "SELECT * FROM `".$name."` WHERE ";

        if(!is_array($conditionArray))
            $conditionArray = is_numeric($conditionArray) ? ["id" => $conditionArray] : false;

        self::_buildQueryWhere($prepareStatement, $conditionArray);

        $prepareStatement .= " LIMIT 1";

        return MySQL_Core::fetchQueryObj($prepareStatement, $conditionArray, PDO::FETCH_OBJ, $name);

    }

    //Static version of getList
    public static function loadMultiple($conditionArray = null){

        self::dataToArray($conditionArray);
        $name = self::name();

        $prepareStatement = "SELECT * FROM ".$name;

        if($conditionArray !== null){
            $prepareStatement .= " WHERE ";
            self::_buildQueryWhere($prepareStatement, $conditionArray);
        }

        return MySQL_Core::fetchQueryObj($prepareStatement, $conditionArray, PDO::FETCH_CLASS, $name);

    }

    //-------------DB Delete Objects

    /**
     * 100 Queries Run
     * <p>Average Time: 2ms per 100/1.265kb</p>
     */

    public function remove(){
        return self::destroy($this->id);
    }

    public static function removeMultiple($conditionArray){

        $prepareStatement = "DELETE FROM ".self::name()." WHERE ";

        self::_buildQueryWhere($prepareStatement, $conditionArray);

        return MySQL_Core::fetchQuery($prepareStatement, $conditionArray);

    }

    /**
     * 100 Queries Run
     * <p>Average Time: 38ms per 100/0.325kb</p>
     */

    public static function destroy($id){

        $prepareStatement = "DELETE FROM ".self::name()." WHERE id = :id";
        $dataArray = [':id' => $id];

        return MySQL_Core::fetchQuery($prepareStatement, $dataArray);

    }


    //-------------Object Related

    //gets the first object occurrence or creates a new one in the database
    public static function firstOrCreate($dataArray){

        $obj = self::firstOrNew($dataArray);

        if(is_object($obj)){
            if(!is_numeric($obj->id))
                $obj->createNew();
        }

        return $obj;

    }

    //gets the first object occurrence or returns a new instance of that object
    /*
     * @return DatabaseObject $dataArray
     */
    public static function firstOrNew($dataArray){

        $obj = self::loadSingle($dataArray);

        return !is_object($obj) ? self::newInstance($dataArray) : $obj;

    }

    /**
     * @param $functionCall
     * What is added (orderBy, limit, groupBy) can be array or string
     *
     * @param null $params
     * The parameters of the query ('10', 'id ADC') can be array or string
     *
     * @return mixed - returns new instance of self
     *
     * Examples:
     * Object::query('limit', 10)->getList();
     * Object::query(['orderBy', 'limit'], ['id DESC', 10])->getList();
     * Object::query(['orderBy', 'limit'], ['id ASC', 10])->getList();
     * Object::query(['orderBy' => 'id ASC', 'limit' => 10])->getList();
     */

    public static function query($functionCall, $params = null){

        if($params === null){
            if(!is_array($functionCall))
                trigger_error("Invalid Use of Query. No Params Given, and invalid Key/Value pairs");

            foreach($functionCall as $key => $value){
                if($key == 'groupBy' or $key == 'orderBy' or $key == 'limit')
                    MySQL_Core::getInstance()->query->$key($value);
            }

        }else{
            if(!is_array($functionCall))
                MySQL_Core::getInstance()->query->$functionCall($params);
            else{
                foreach($functionCall as $key => $value){
                    MySQL_Core::getInstance()->query->$value($params[$key]);
                }
            }
        }

        return self::newInstance();
    }

    private static function _buildQuerySet(&$prepareStatement, &$conditionArray, &$keyChain = null){

        if($keyChain === null)
            $keyChain = self::getKeyChain();

        foreach($conditionArray as $key => $val){
            if($val !== null && $key !== "id" && array_key_exists($key, $keyChain))
                $prepareStatement .= "$key = :$key, ";
            else
                unset($conditionArray[$key]);
        }

        $prepareStatement = rtrim($prepareStatement, ", ");

    }

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

class QueryHandler{

    private $groupBy = "";
    private $orderBy = "";
    private $limit = "";

    //Any time a query is executed, we want to make sure to clear the query so that it doesn't show up again.
    public function getQuery(){

        $query = " {$this->groupBy} {$this->orderBy} {$this->limit} ";

        $this->groupBy = "";
        $this->orderBy = "";
        $this->limit = "";

        return $query;

    }

    public function groupBy($grouping){

        $this->$groupBy = "GROUP BY $grouping";

        return $this;

    }

    public function orderBy($order){

        $this->orderBy = "ORDER BY $order";

        return $this;

    }

    public function limit($limit){

        $min = null;
        $max = null;

        if(is_array($limit)){

            //if array has named keys
            if(array_key_exists('min', $limit))
                $min = $limit['min'];
            if(array_key_exists('max', $limit))
                $max = $limit['max'];

            //if array uses integers instead
            if(array_key_exists(0, $limit))
                $min = $limit[0];
            if(array_key_exists(1, $limit))
                $max = $limit[1];

        }else{
            $min = intval($limit);
        }

        if($min === null)
            return $this;

        $this->limit = "LIMIT $min, ";

        if($max !== null)
            $this->limit .= "$max, ";

        $this->limit = rtrim($this->limit, ", ");

        return $this;

    }

}

//Core is a singleton because it implements the database connection class. Calling core multiple times
//would otherwise create many more objects then if we didn't have a singleton as a core.
//Also, creating a singleton means we can save data to the query using our query handler
//between instance calls.
class MySQL_Core implements Database_Core{

    public $dbh;
    public $query;
    private static $instance;
    //Core is a singleton

    public function __construct(){

        $dsn = 'mysql:host=' . Config::read('db.host') .
            ';dbname='    . Config::read('db.name') .
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

                if(!is_object($fetchParam) && !is_array($fetchParam))
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

