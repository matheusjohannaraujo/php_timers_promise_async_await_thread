<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_work_promise
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-23
*/

//declare(ticks=1);
require_once "work.php";

echo "Inicio\r\n";

$i = 1;

$uid = setInterval(function() use (&$i) {
    echo "Contador " . $i++ . "\r\n";
}, 100);

setTimeout(function() {
    echo "Metade\r\n";
}, 1000);

setTimeout(function() use ($uid) {
    echo "Para Contador\r\n";
    clearInterval($uid);
}, 2000);

echo "Processamento...\r\n";

$count = workWait(function() { usleep(1); });
echo "workRun foi executado $count vezes\r\n";

echo "Fim\r\n";
