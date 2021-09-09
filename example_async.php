<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

require_once "lib/code.php";

echo "Start", PHP_EOL;

$max_sleep = 5;

for ($i = 1; $i <= 25; $i++) {
    async(function() use ($max_sleep) {
        echo $sleep = rand(2, $max_sleep);
        sleep($sleep);
    })->then(function($val) use ($i) {
        echo "The async ${i} function took ${val} seconds to run", PHP_EOL;
    });
}

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun has been run ${count} times", PHP_EOL;

echo "End", PHP_EOL;
