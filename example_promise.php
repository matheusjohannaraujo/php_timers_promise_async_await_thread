<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

require_once "lib/code.php";

echo "Start", PHP_EOL;

$promise = new Promise(function($resolve, $reject) {
    $call = rand(0, 1) ? $resolve : $reject;
    setTimeout(function() use($call) {
        $call("message");
    }, 1000);
});

function info_promise() {
    global $promise;
    echo "> monitor: ", $promise->getMonitor(), PHP_EOL;
    echo "> state: ", $promise->getState(), PHP_EOL;
}

info_promise();

$promise->then(function($result) {
    echo "then (${result})", PHP_EOL;
    info_promise();
})->catch(function($error) {
    echo "catch (${error})", PHP_EOL;
    info_promise();
})->finally(function() {
    echo "finally", PHP_EOL;
    info_promise();
});

echo "Processing...", PHP_EOL;
for ($counter = 0; $counter < 10; $counter++) {
    echo "Counter: ${counter}", PHP_EOL;
    usleep(200000);
}

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun has been run ${count} times", PHP_EOL;

echo "End", PHP_EOL;
