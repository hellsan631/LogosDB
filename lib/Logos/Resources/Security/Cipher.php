<?php

/**
 * Class Cipher
 */
class Cipher {
    private $_secureKey;
    private $_iv;

    function __construct($textKey, $iv = null) {
        $this->_secureKey = hash('sha256', $textKey, TRUE);

        if($iv == null)
            $this->_iv = mcrypt_create_iv(32);
        else
            $this->_iv = base64_decode($iv);

    }

    /**
     * Encrypts a piece of text with a given text key
     * @param $input
     *
     * @return string
     * Returns
     */

    public function encrypt($input) {
        return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $this->_secureKey, $input, MCRYPT_MODE_ECB, $this->_iv));
    }

    public function decrypt($input) {
        return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $this->_secureKey, base64_decode($input), MCRYPT_MODE_ECB, $this->_iv));
    }

    public function getIV(){
        return base64_encode($this->_iv);
    }

    public static function getRandomKey($length = 22){
        return substr(str_replace('+', '.', base64_encode(openssl_random_pseudo_bytes(64))), 0, $length);
    }

}
