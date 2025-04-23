<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

use MJohann\Packlib\Timers;

require_once "../vendor/autoload.php";

echo "Start", PHP_EOL;

$counter = 1;

$id = Timers::setInterval(function () use (&$counter) {
    echo "Counter: ", $counter++, PHP_EOL;
}, 100);

Timers::setTimeout(function () {
    echo "Half of the increments", PHP_EOL;
}, 1000);

Timers::setTimeout(function () use ($id) {
    echo "Stopping the counter", PHP_EOL;
    Timers::clearInterval($id);
}, 2000);

echo "Processing...", PHP_EOL;

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = Timers::workWait(function () {
    usleep(1);
});
echo "workRun has been run " . $count . " times", PHP_EOL;

echo "End", PHP_EOL;
