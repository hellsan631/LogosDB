<?php

include "db-handler-mysql.php";

//example object
class User extends DatabaseObject{

    public $id; //already defined in the DatabaseObject class.

    public $username;
    public $email;

}

class Event extends DatabaseObject{

    public $id; //already defined in the DatabaseObject class.

    public $date_start;
    public $name;

}



?>