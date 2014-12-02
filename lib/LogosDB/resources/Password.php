<?php

class Password{

    private $_key;
    private $_salt;
    private $_options;

    public function __construct($key, $options = array('salt' => null, 'cost' => 11, 'hashed' => false)) {

        if(!isset($options['salt']))
            $options['salt'] = null;
        if(!isset($options['cost']))
            $options['cost'] = 11;
        if(!isset($options['hashed']))
            $options['hashed'] = false;

        if($options['salt'] == null)
            $this->generateSalt();
        else
            $this->_salt = $options['salt'];

        $this->_options = array('cost' => $options['cost'], 'salt' => $this->_salt);

        if($options['hashed'] === false)
            $this->generatePassword($key);
        else
            $this->_key = $key;

    }

    private function generateSalt(){
        $this->_salt = Cipher::getRandomKey();
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

    public function getSalt(){
        return $this->_salt;
    }

}