<?php

/**
 * Class Iron
 *
 * Helps protect against Cross Site Request Forgery attacks.
 */
class Iron{

    private static $token_id;
    private static $token_value;

    public function __construct(){

        if(!session_id())
            session_start();



    }

    public function get_token_id(){

        if(!isset(self::$token_id))
            $_SESSION['token_id'] = self::$token_id = Cipher::getRandomKey(10);

        return self::$token_id;

    }

    public function get_token() {
        if(isset($_SESSION['token_value'])) {
            return $_SESSION['token_value'];
        } else {
            $token = hash('sha256', $this->random(500));
            $_SESSION['token_value'] = $token;
            return $token;
        }

    }

    public function check_valid($method) {
        if($method == 'post' || $method == 'get') {
            $post = $_POST;
            $get = $_GET;
            if(isset(${$method}[$this->get_token_id()]) && (${$method}[$this->get_token_id()] == $this->get_token())) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function form_names($names, $regenerate) {

        $values = array();
        foreach ($names as $n) {
            if($regenerate == true) {
                unset($_SESSION[$n]);
            }
            $s = isset($_SESSION[$n]) ? $_SESSION[$n] : Cipher::getRandomKey(10);
            $_SESSION[$n] = $s;
            $values[$n] = $s;
        }
        return $values;
    }


}