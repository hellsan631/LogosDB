<?php

    $time = microtime(TRUE);
    $mem = memory_get_usage();

    include "./_db/objects.php";

    use Logos\Objects\User;
    use Logos\Main\Config;

    Config::write('db.host', 'localhost');
    Config::write('db.base', 'dimlitl_sidekick');//whatsyo1_thepool - thepool
    Config::write('db.user', 'dimlitl_prax');//whatsyo1_thepool - dimlitl_prax
    Config::write('db.password', 'Radegast123/*');//P*OuT51Nq_T3 - Radegast123/*

    $memTwo = memory_get_usage();

    $count = 0;
    $users = [];

    while($count < 100){

        //User::returnInstance(["username" => "testing", "email" => "email@email.com"])->createNew();

        User::createSingle(["username" => "testing", "email" => "email@email.com"]);

        $count++;

    }

    $memThree = memory_get_usage();

    echo "<pre>";

    $memTemp = number_format((($memTwo-$mem) / 1024), 2);
    $memTwoTemp = number_format((($memThree-$memTwo) / 1024), 2);
    $memThreeTemp = number_format(((memory_get_usage() - $memTwo) / 1024), 2);
    $timeTemp =  number_format((microtime(TRUE) - $time), 6);

    echo "Head : $memTemp KB<br/>";
    echo "Page : $memTwoTemp KB <br/>";
    echo "Rend : $memThreeTemp KB <br/>";
    echo "Time: $timeTemp sec";

    echo "</pre>";


?>