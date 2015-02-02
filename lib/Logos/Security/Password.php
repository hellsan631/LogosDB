<?php

namespace Logos\Security;

class Password{

    private $_key;
    private $_options;

    public function __construct($key, $options = ['cost' => 11, 'hashed' => false]) {

        if(!isset($options['cost']))
            $options['cost'] = 11;
        if(!isset($options['hashed']))
            $options['hashed'] = false;

        if($options['hashed'] === false)
            $this->generatePassword($key);
        else
            $this->_key = $key;

    }

    private function generatePassword($password){
        $this->_key = password_hash($password, PASSWORD_BCRYPT, $this->_options);
    }

    public function checkPassword($password){
        return password_verify($password, $this->_key);
    }

    public function getKey(){
        return $this->_key;
    }

}