<?php

    namespace Logos;

    include "./_db/objects.php";

    use Logos\DB\MySQL\Core;
    use Logos\Objects;
    use Logos\Objects\User;
    use Logos\Main\Config;
    use Logos\Main;

    Config::write('db.host', 'localhost');
    Config::write('db.base', 'db_name');
    Config::write('db.user', 'db_user');
    Config::write('db.password', 'db_pass');

    $timeArray = [];

    timeFunction(function(){

        $count = 0;
        $users = [];

        while($count < 100){
            array_push($users, new User(["username" => "testing", "email" => "email@email.com"]));
            $count++;
        }

        foreach($users as $user){
            $user->createNew();
        }

    }, "createNew x 100", $timeArray);

    timeFunction(function(){

        $count = 0;

        while($count < 100){
            User::newInstance(["username" => "testing", "email" => "email@email.com"])->createNew();
            $count++;
        }

    }, "Instance::createNew x 100", $timeArray);

    timeFunction(function(){

        $count = 0;

        while($count < 100){
            User::createSingle(["username" => "testing", "email" => "email@email.com"]);
            $count++;
        }

    }, "createSingle x 100", $timeArray);

    timeFunction(function(){

        return User::createMultiple(["username" => "testing", "email" => "email@email.com"], 100);

    }, "createMultiple x 100", $timeArray);

    timeFunction(function(){

        $users = [];
        $count = 0;

        while($count < 100){
            array_push($users, ["username" => "testing", "email" => "email@email.com"]);
            $count++;
        }

        return User::createMultiple($users);

    }, "createMultiple (arrays) x 100", $timeArray);

    timeFunction(function(){

        $count = 0;
        $user = new User(["id" => 3939, "username" => "testing", "email" => "email@email.com"]);

        while($count < 100){
            $user->save(["username" => "testing"]);
            $count++;
        }

    }, "save x 100", $timeArray);

    timeFunction(function(){

        User::query(['limit'], [100]);

        return User::saveMultiple(["email" => "email@email.com"], ["username" => "testing"]);

    }, "saveMultiple x 100", $timeArray);

    timeFunction(function(){

        $count = 0;
        $user = new User();

        while($count < 100){
            $user->loadInto(3939);
            $count++;
        }

    }, "loadInto x 100", $timeArray);

    timeFunction(function(){

        User::query('limit', 100)->getList();

    }, "getList x 100", $timeArray);

    timeFunction(function(){

        $count = 0;

        while($count < 100){
            User::load(["id" => 3939]);
            $count++;
        }

    }, "load x 100", $timeArray);

    timeFunction(function(){

        User::query(['limit'], [100]);

        User::loadMultiple(["username" => "testing"]);

    }, "loadMultiple x 100", $timeArray);

    timeFunction(function(){

        User::query(['limit'], [100]);

        $users = User::loadMultiple(["username" => "testing"]);

        foreach($users as $user){
            $user->remove();
        }

    }, "remove x 100", $timeArray);


    timeFunction(function(){

        User::query(['limit'], [100]);

        $users = User::loadMultiple(["username" => "testing"]);

        foreach($users as $user){
            User::destroy($user->id);
        }

    }, "destroy x 100", $timeArray);

    timeFunction(function(){

        User::query('limit', 300);

        return User::removeMultiple(["username" => "testing"]);


    }, "removeMultiple x 100", $timeArray);


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


?>