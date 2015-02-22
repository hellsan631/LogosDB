<?php

include "./vendor/autoload.php";

$timeArray = [];

timeFunction(function(){
        $count = 0;
        while($count < 1000){
            MySQL_User::newInstance(["username" => "testing"]);
            $count++;
        }
}, "newInstance x 1000", $timeArray);

foreach($timeArray as $value){
	echo "<pre>";
	var_dump($value);
	echo "</pre>";
}
	
function timeFunction($function, $name, &$timeArray){
	$time =  microtime(TRUE);
	$mem = memory_get_usage();
	$return = $function();
	array_push($timeArray,
		array(
			"Time" => number_format((microtime(TRUE) - $time)*1000, 3)." sec",
			"Memory" => number_format(((memory_get_usage() - $mem) / 1024), 4)." kb",
			"Name" => $name,
			"Return" => $return
		)
	);
}