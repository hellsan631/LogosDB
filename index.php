<?php

    $time = microtime(TRUE);
    $mem = memory_get_usage();

    $memTwo = memory_get_usage();

    print_r(get_loaded_extensions());

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