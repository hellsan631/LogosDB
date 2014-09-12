<?php

    namespace Logos\DB;

    interface DatabaseHandler{

        //-------------DB Object Creation

        //create new object in database with current given object
        public function createNew();

        //static version of createNew based on a given input of data, to be inserted into class
        public static function createSingle($data);

        //Allows you to do a multiple object insert into a database.
        //Count field is optional, as count might depend on the number of rows in the array
        //Ideally, $data should be a numerically index array of associative arrays relating to each object being created
        //However, if you wish to create a number N of identical objects, data can be a single associative array instead
        public static function createMultiple($data, $count);


        //-------------DB Object Update

        //Updates/saves changes to an object in the database, with an optional param $changedData
        public function save($changedData);

        //Updates multiple objects with given data, and a conditional array
        public static function saveMultiple($changedData, $conditionArray);


        //-------------DB Load Objects

        //Loads an object from a database into the container class
        public function loadInto($id);

        //Loads a single object from the database
        public static function load($conditionArray);

        //Loads a list of objects from the database with given conditions
        public static function getList($conditionArray);


        //-------------DB Delete Objects

        //Deletes/Removes/Erases a single object
        public function remove();

        //Deletes/Removes/Erases multiple objects based on a set of conditions
        public static function removeMultiple($conditionArray);

        //Deletes/Removes/Erases an object based on an ID (can be an array)
        public static function destroy($id);


        //-------------DB Caching Functions

        //caches the object inside the $_SESSION variable
        public function cache($cache_name, $timer);

        //finds a cached object or database
        public static function find($conditionArray);


        //-------------Object Related

        //gets the first object occurrence or creates a new one in the database
        public static function firstOrCreate($dataArray);

        //gets the first object occurrence or returns a new instance of that object
        public static function firstOrNew($dataArray);

        //returns an instance of an object with a given array of data
        public static function newInstance($dataArray);

    }


?>