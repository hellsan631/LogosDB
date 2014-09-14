<?php

    namespace Logos;

    $time = microtime(TRUE);
    $mem = memory_get_usage();

    include "./_db/objects.php";

    use Logos\DB\MySQL\Core;
    use Logos\Objects;
    use Logos\Objects\User;
    use Logos\Main\Config;
    use Logos\Main;

    Config::write('db.host', 'localhost');
    Config::write('db.base', 'dimlitl_sidekick');
    Config::write('db.user', 'dimlitl_prax');
    Config::write('db.password', 'Radegast123/*');

    $timeTemp2 = number_format((microtime(TRUE) - $time)*1000, 3);
    $time2 = microtime(TRUE);
    $memTemp = number_format(((memory_get_usage()-$mem) / 1024), 2);
    $memTwo = memory_get_usage();

    $count = 0;

    while ($count < 100){
        $count++;
    }

    $timeTemp1 =  number_format((microtime(TRUE) - $time2)*1000, 3);
    $time3 = microtime(TRUE);

    $count = 0;

    $memTwoTemp = number_format(((memory_get_usage() - $memTwo) / 1024), 2);
    $memTwo = memory_get_usage();

    //big method goes here

    $timeTemp3 = number_format((microtime(TRUE) - $time3)*1000, 3);

    $memThree = memory_get_usage();

    echo "<pre>";

    $memThreeTemp = number_format((($memThree-$memTwo) / 1024), 2);

    $timeTemp =  number_format((microtime(TRUE) - $time)*1000, 3);


    echo "Head Time : $timeTemp2 ms<br/>";
    echo "Head : $memTemp KB<br/><br/>";
    echo "Mem 1 : $memTwoTemp KB <br/>";
    echo "Time 1: $timeTemp1 ms<br/><br/>";
    echo "Mem 2 : $memThreeTemp KB <br/>";
    echo "Time 2: $timeTemp3 ms<br/><br/>";
    echo "Time Total: $timeTemp ms<br/>";

    echo "</pre>";


?>