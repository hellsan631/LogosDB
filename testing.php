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

    var_dump(opcache_get_configuration());


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
