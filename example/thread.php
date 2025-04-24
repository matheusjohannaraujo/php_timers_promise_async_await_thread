<?php

// Use TICK to catch system signals at every tick (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\WebThread;

require_once "../vendor/autoload.php";

// Initialize WebThread with the RPC endpoint and secret key
WebThread::init("http://localhost/rpc.php", "secret");

echo "Start", PHP_EOL;

// Random sleep time between 0 and 2 seconds
$sleep = rand(0, 2);
echo "Sleep: {$sleep}s", PHP_EOL;

// === SYNC EXECUTION ===
// Waits for the return of the function executed in parallel
$responseSync = WebThread::threadParallel(function () use ($sleep) {
	sleep($sleep);
	echo "Ok 1";
});

echo "Response (sync): ";
var_export($responseSync["response"]);
echo PHP_EOL;

// === ASYNC EXECUTION ===
// If execution takes less than 2000ms, the result is returned;
// otherwise, the script continues and no result is returned
$responseAsync = WebThread::threadParallel(function () use ($sleep) {
	sleep($sleep);
	echo "Ok 2";
}, false);

echo "Response (async): ";
var_export($responseAsync["response"]);
echo PHP_EOL;

// === PROMISE EXECUTION ===
// Sends a parallel task as a promise and waits manually for the result
$promise = WebThread::threadParallel(function () use ($sleep) {
	sleep($sleep);
	echo "Ok 3";
}, true, true);

$responsePromise = WebThread::await($promise);

echo "Response (promise): ";
var_export($responsePromise["response"]);
echo PHP_EOL;

// === TIMED WORK ===
// Executes a lightweight timed task and returns how many times it was run
$count = WebThread::workWait(function () {
	usleep(1);
});

echo "workWait executed {$count} times", PHP_EOL;
echo "End", PHP_EOL;
