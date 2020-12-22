<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-22
*/

require_once "Promise.php";

echo "Inicio\r\n";

$p = new Promise(function($res, $rej) {
    setTimeout(function() use($res) {
        $res("ok");
    }, 1000);
    //$rej("error");
});

$p->then(function($v) {
    echo "then ", $v, "\r\n";
})->catch(function($v) {
    echo "catch ", $v, "\r\n";
})->finally(function(){
    echo "finally\r\n";
});

echo "Processamento...\r\n";

workWait();

echo "Fim\r\n";
