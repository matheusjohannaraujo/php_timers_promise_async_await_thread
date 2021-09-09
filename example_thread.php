<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

require_once "lib/code.php";

echo "Start", PHP_EOL;

$sleep = rand(0, 3);

echo "Sleep: ", $sleep, "s", PHP_EOL;

// THREAD SYNC (Wait for the return of the execution of the passed function)
$sync = thread_parallel(function() use ($sleep) {
	sleep($sleep);
	echo "Ok 1";
});

var_export($sync["response"]);

echo PHP_EOL;

// THREAD ASYNC (If the callback execution takes less than 500ms, the execution result is returned,
// otherwise the script continues to run without the sender receiving a return response)
$async = thread_parallel(function() use ($sleep) {
	sleep($sleep);
	echo "Ok 2";
}, false);

var_export($async["response"]);

echo PHP_EOL;

// THREAD SYNC (Does not wait for the return of the execution of the passed function)
$promise = thread_parallel(function() use ($sleep) {
	sleep($sleep);
	echo "Ok 3";
}, true, true);

var_export(await($promise)["response"]);

echo PHP_EOL;

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun has been run ${count} times", PHP_EOL;

echo "End", PHP_EOL;
