<?php

// Use TICK to catch system signals at every tick (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\WebThread;
use function MJohann\Packlib\Functions\{await, workWait};

require_once "../vendor/autoload.php";

// Initialize WebThread with the RPC endpoint and secret key
WebThread::init("http://localhost:8080/rpc.php", "secret");

echo "Start", PHP_EOL;

$promise1 = WebThread::rpcSend(
	function () {
		sleep(5);
		echo "Ok 1";
	}
);

$promise2 = WebThread::rpcSend(
	function () {
		sleep(3);
		echo "Ok 2";
	}
);

$promises = [$promise1, $promise2];

foreach ($promises as $key => $promise) {
	$promise
		->then(function ($result) {
			echo "then: ", $result, PHP_EOL;
		})
		->catch(function ($error) {
			echo "catch: ", $error, PHP_EOL;
		})
		->finally(function () {
			echo "finally", PHP_EOL, PHP_EOL;
		});
}

$promise3 = WebThread::rpcSend(
	function () {
		sleep(1);
		echo "Ok 3";
	}
);

try {
	$response = await($promise3);
	echo "await: ", $response, PHP_EOL;
} catch (\Throwable $th) {
	echo "catch: ", $th->getMessage(), PHP_EOL;
}

// === TIMED WORK ===
// Executes a lightweight timed task and returns how many times it was run
$count = workWait(function () {
	usleep(1);
});

echo "workWait executed {$count} times", PHP_EOL;
echo "End", PHP_EOL;
