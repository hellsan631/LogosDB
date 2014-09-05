<?php

$time = microtime(TRUE);
$mem = memory_get_usage();

if(!session_id())
    session_start();

include "./_db/cache/phpfastcache.php" ;
include "./_db/objects.php";
include "./_db/security.php";

$memTwo = memory_get_usage();

$cache = phpFastCache("files");

use Logos\Objects\User;
use Logos\Security\Cipher;

$count = 0;
$users = [];

while($count < 10){

    $user = new User(array("username" => "x".mt_rand(0, 100000)));

    array_push($users, $user->username);

    $_SESSION[$user->username] = json_encode($user->toArray());

    //$cache->set($user->username, json_encode($user->toArray()), 300);

    $count++;
}

$memThree = memory_get_usage();

foreach($users as $value){

    echo $_SESSION["$value"]."<br/>";

    //echo $cache->get("$value")."<br/>";

}


echo "<pre>";

$memTemp = number_format((($memTwo-$mem) / 1024), 2);
$memTwoTemp = number_format((($memThree-$memTwo) / 1024), 2);
$memThreeTemp = number_format(((memory_get_usage() - $memTwo) / 1024), 2);
$timeTemp =  number_format((microtime(TRUE) - $time), 6);

$cache->clean();

echo "Head : $memTemp KB<br/>";
echo "Page : $memTwoTemp KB <br/>";
echo "Rend : $memThreeTemp KB <br/>";
echo "Time: $timeTemp sec";

echo "</pre>";
