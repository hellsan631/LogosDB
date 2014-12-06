<?php

/**
 * Class Iron
 *
 * Helps protect against Cross Site Request Forgery attacks.
 */

class Iron{

    private static $_token_id;
    private static $_token_value;
    private static $_instance;

    public function __construct(){

        $this->_generate_token_data();

    }

    /**
     * Creates A Singleton from this class.
     *
     * @return self
     */

    public static function getInstance(){
        if (!isset(self::$_instance)){
            $object = get_called_class();
            self::$_instance = new $object();
        }

        return self::$_instance;
    }

    /**
     * Used for echoing a form input for a post request
     *
     * @return void
     */

    public function generate_post_token(){

        $data = $this->_generate_token_data();

        echo "<input type='hidden' name='{$data['id']}' value='{$data['value']}' />";

    }

    /**
     * Used for echoing a part of a url for a get token
     *
     * @return void
     */

    public function generate_get_token(){

        $data = $this->_generate_token_data();

        echo "&{$data['id']}={$data['value']}";

    }

    /**
     * Used to generate new token data each time we want a token
     *
     * @return array
     */

    private function _generate_token_data(){
        self::$_token_id = $_SESSION['token_id'] = urlencode(Cipher::getRandomKey(8));
        self::$_token_value = $_SESSION[self::$_token_id] = urlencode(Cipher::getRandomKey());

        return ["id" => self::$_token_id, "value" => self::$_token_value];
    }

    public function check_token(){

        if(!$this->check_valid($_SERVER['REQUEST_METHOD']))
            return false;

        if($_SERVER['REQUEST_METHOD'] == 'POST'){
            if(!isset($_POST[$this->get_token_id()]))
                return false;

            if($_POST[self::$_token_id] !== $this->get_token_value())
                return false;

        }else if($_SERVER['REQUEST_METHOD'] == 'GET'){
            if(!isset($_GET[$this->get_token_id()]))
                return false;

            if($_GET[self::$_token_id] !== $this->get_token_value())
                return false;

        }

        return true;
    }


    public function get_token_id(){

        if(!isset($_SESSION['token_id']))
            $_SESSION['token_id'] = urlencode(Cipher::getRandomKey(8));

        return $_SESSION['token_id'];

    }

    public function get_token_value() {

        if(!isset(self::$_token_id))
            trigger_error("Construct Not Called on Iron Class");

        if(!isset($_SESSION[self::$_token_id]))
            $_SESSION[self::$_token_id] = urlencode(Cipher::getRandomKey());

        return $_SESSION[self::$_token_id];

    }

    public function check_valid($method) {

        $method = strtolower($method);

        if($method == 'post' || $method == 'get'){
            if(isset(${$method}[$this->get_token_id()]) and (${$method}[$this->get_token_id()] == $this->get_token_value()))
                return true;

        }

        return false;
    }


}