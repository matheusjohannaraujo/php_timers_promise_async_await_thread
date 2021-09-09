<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

require_once "lib/code.php";

echo "Start", PHP_EOL;

for ($i = 1; $i <= 25; $i++) {
    Promise::async(function($resolve, $reject) {
        $sleep = rand(2, 5);
        sleep($sleep);
        (rand(0, 1) ? $resolve : $reject)($sleep);
    })->then(function($val) use ($i) {
        echo "Promise ${i} resolved ${val} seconds", PHP_EOL;
    })->catch(function($val) use ($i) {
        echo "Promise ${i} rejected ${val} seconds", PHP_EOL;
    });
}

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun has been run ${count} times", PHP_EOL;

echo "End", PHP_EOL;
