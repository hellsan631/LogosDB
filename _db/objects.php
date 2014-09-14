<?php

namespace Logos\Objects;

include "db-handler-mysql.php";

use Logos\DB\MySQL;
use Logos\DB\MySQL\DatabaseObject;

//include "backend.php";

class User extends DatabaseObject{

    public $username;
    public $email;
    public $rsi_community;
    public $rsi_handle;
    public $age;
    public $timezone;
    public $forum_id;
    public $permission_level;
    public $apply_url;

}



?>