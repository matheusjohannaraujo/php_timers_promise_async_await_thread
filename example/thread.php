<?php

// Use TICK to catch system signals at every tick (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\WebThread;
use function MJohann\Packlib\Functions\{await, workWait};

require_once "../vendor/autoload.php";

// Initialize WebThread with the RPC endpoint and secret key
WebThread::init("http://localhost/rpc.php", "secret");

echo "Start", PHP_EOL;

// Random sleep time between 0 and 3 seconds
$sleep = rand(0, 3);
echo "Sleep: {$sleep}s", PHP_EOL;

// Sends a parallel task as a promise and waits manually for the result
$promise = WebThread::rpcSend(function () use ($sleep) {
	sleep($sleep);
	echo "Ok 1";
});

$promise
	->then(function ($result) use ($promise) {
		echo "then: ", PHP_EOL;
		var_dump($result);
	})
	->catch(function ($error) use ($promise) {
		echo "catch: ", PHP_EOL;
		var_dump($error);
	})
	->finally(function () use ($promise) {
		echo "finally", PHP_EOL;
	});

/*$responsePromise = await($promise);
echo "Response (promise): ";
var_export($responsePromise);
echo PHP_EOL;*/

// === TIMED WORK ===
// Executes a lightweight timed task and returns how many times it was run
$count = workWait(function () {
	usleep(1);
});

echo "workWait executed {$count} times", PHP_EOL;
echo "End", PHP_EOL;
