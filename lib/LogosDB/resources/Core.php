<?php

class Core{

    /**
     * Cleans some data of escape characters and special html characters
     *
     * @param String $data
     * Data to clean
     *
     * @return string
     */
    public static function clean($data){
        return htmlspecialchars(mysql_real_escape_string($data));
    }

    /**
     * Checks if a given string is JSON or not
     *
     * @param $string
     *
     * @return bool
     */

    public static function isJson($string){

        if (!is_string($string))
            return false;

        // trim white spaces
        $string = trim($string);

        // get first character/last character
        $firstChar = substr($string, 0, 1);
        $lastChar = substr($string, -1);

        if (!$firstChar || !$lastChar)
            return false;

        if ($firstChar !== '{' && $firstChar !== '[')
            return false;

        if ($lastChar !== '}' && $lastChar !== ']')
            return false;

        // let's leave the rest to PHP.
        json_decode($string);

        return (json_last_error() === JSON_ERROR_NONE);
    }

    /**
     * Sends an email with correct headers
     * @param $subject
     * @param $message
     * @param $recipient
     * @param string $from
     * @return bool
     */
    public static function sendEmail($subject, $message, $recipient, $from = "no-reply<noreply@email.com>"){

        $headers = "From: $from\r\n";
        $headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

        return mail($recipient, $subject, $message, $headers);

    }

    /**
     * Sorts an array of objects by an objects key with a given order
     *
     * @param $objects
     * Array of objects to sort
     *
     * @param $key
     * @param string $order
     * @return mixed
     */

    public static function sortByKey(&$objects, $key, $order = "DESC"){

        global $sortKey;

        $sortKey = $key;

        $order = mb_strtoupper($order);

        if($order === "ASC"){
            usort($objects, function($a, $b){

                global $sortKey;

                return strcmp($a->{$sortKey}, $b->{$sortKey});

            });
        }else if($order === "DESC"){
            usort($objects, function($a, $b){

                global $sortKey;

                return strcmp($b->{$sortKey}, $a->{$sortKey});

            });
        }

        return $objects;

    }


    /**
     * Returns a base_64 encoded url
     * @param $input
     * @return string
     */
    public static function base64_url_encode($input){
        return strtr(base64_encode($input), '+/=', '-_,');
    }

    /**
     * Formats a number with an ordinal suffix
     * @param $number
     * @param string $type
     * @return string
     */
    public static function formatNumber($number, $type = "ordinal"){

        $ends = array('th','st','nd','rd','th','th','th','th','th','th');

        if($number == "N/A")
            return $number;

        return (($number %100) >= 11 && ($number%100) <= 13) ? $number.'th' : $number.$ends[$number % 10];

    }

    /**
     * Gets the current timezone
     *
     * @return DateTimeZone
     */
    public static function getTimezone(){
        return new DateTimeZone('America/New_York');
    }

    /**
     * Checks to see if a string is a valid date time
     * @param $str_dt
     * DateTime string
     *
     * @return bool
     */
    public static function isValidDateTimeString($str_dt) {

        if(!is_string($str_dt))
            return false;

        try{
            $date = new DateTime($str_dt);
        }catch(Exception $e){
            return false;
        }

        return $date &&
            DateTime::getLastErrors()["warning_count"] == 0 &&
            DateTime::getLastErrors()["error_count"] == 0;

    }

    /**
     * Converts a time to a correctly formatted Unix Timestamp, to be inserted into a database
     *
     * @param $timestamp
     *
     * @return bool|string
     */

    public static function unixToMySQL($timestamp){

        if($timestamp instanceof DateTime)
            $timestamp = $timestamp->format('Y-m-d H:i:s');

        return date('Y-m-d H:i:s', strtotime($timestamp) ?: $timestamp);

    }

    /**
     * Gets the day of a given date time string
     *
     * @param $dateTimeString
     *
     * @return string
     */
    public static function getDay($dateTimeString){

        $tempDate = new DateTime($dateTimeString);
        $tempDate->setTime(0,0,0);
        return $tempDate->format('D');

    }
}

