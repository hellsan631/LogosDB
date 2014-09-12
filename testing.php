<?php

    $time = microtime(TRUE);
    $mem = memory_get_usage();

    $time2 = number_format((microtime(TRUE) - $time)*1000, 3);


    echo "<pre>";

    $memTemp = number_format(((memory_get_usage()-$mem) / 1024), 2);
    $mem = number_format((($mem) / 1024), 2);
    $timeTemp =  number_format((microtime(TRUE) - $time)*1000, 3);


    echo "Mem Head : $mem KB<br/>";
    echo "Time Head: $time2 ms<br/><br/>";

    echo "Total : $memTemp KB<br/>";
    echo "Time Total: $timeTemp ms<br/>";


    echo "</pre>";


