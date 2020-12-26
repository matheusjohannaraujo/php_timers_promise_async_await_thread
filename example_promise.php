<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_work_promise
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-26
*/

declare(ticks=1);
require_once "work.php";
require_once "Promise.php";

echo "Inicio\r\n";

$promise = new Promise(function($resolve, $reject) {
    setTimeout(function() use($resolve) {
        $resolve("ok");
    }, 1000);
    //$reject("error");
});

$promise->then(function($value) {
    echo "then ", $value, "\r\n";
})->catch(function($value) {
    echo "catch ", $value, "\r\n";
})->finally(function(){
    echo "finally\r\n";
});

echo "Processamento...\r\n";
for ($j = 0; $j < 10; $j++) {
    echo "Loop $j\r\n";
    usleep(200000);
}

$count = workWait(function() { usleep(1); });
echo "workRun foi executado $count vezes\r\n";

echo "Fim\r\n";
