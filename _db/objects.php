<?php


class users extends DatabaseObject{

    public $username;
    public $user_email;
    public $rsi_community;
    public $rsi_handle;
    public $age;
    public $timezone;
    public $forum_id;
    public $permission_level;
    public $apply_url;

}

class agents extends users{

    public $password;
    public $salt;

    public function __construct() {

        if(!class_exists('Password')){
            global $ROOT_DB_PATH;
            @include_once "{$ROOT_DB_PATH}security.php";
        }

        $options = array('salt' => $this->salt, 'hashed' => true);

        $this->password = new Password($this->password, $options);

    }

    public function verifyLogin($password){

        return $this->password->checkPassword($password);

    }

}

class reports extends DatabaseObject{

    public $user_id;
    public $report_subject;
    public $report_text;

}

class comments extends DatabaseObject{

    public $user_id;
    public $report_subject;
    public $report_text;

}

class keys extends DatabaseObject{

    public $key;
    public $user_email;

}

class admin_pages extends DatabaseObject{

    public $title;
    public $icon;
    public $template_file;
    public $permission_level;

}

class bugs extends DatabaseObject{

    public $ip_address;
    public $browser;
    public $page_header;
    public $report;
    public $email;
    public $date;

}



?>