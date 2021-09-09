<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

require_once "lib/code.php";

echo "Start", PHP_EOL;

$counter = 1;

$uid = setInterval(function() use (&$counter) {
    echo "Counter: ", $counter++, PHP_EOL;
}, 100);

setTimeout(function() {
    echo "Half of the increments", PHP_EOL;
}, 1000);

setTimeout(function() use ($uid) {
    echo "Stopping the counter", PHP_EOL;
    clearInterval($uid);
}, 2000);

echo "Processing...", PHP_EOL;

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun has been run ${count} times", PHP_EOL;

echo "End", PHP_EOL;
