<?php

// Use TICK to catch system signals at every tick (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\WebThread;
use function MJohann\Packlib\Functions\{await, workWait};

require_once "../vendor/autoload.php";

// Initialize WebThread with the RPC endpoint and secret key
WebThread::init("http://localhost/rpc.php", "secret");

echo "Start", PHP_EOL;

$p1 = WebThread::rpcSend(
	function () {
		sleep(5);
		echo "Ok 1";
	}
);

$p2 = WebThread::rpcSend(
	function () {
		sleep(3);
		echo "Ok 2";
	}
);

$promises = [$p1, $p2];

foreach ($promises as $key => $promise) {
	$promise
		->then(function ($result) {
			echo "then: ", PHP_EOL;
			var_export($result);
		})
		->catch(function ($error) {
			echo "catch: ", PHP_EOL;
			var_export($error);
		})
		->finally(function () {
			echo PHP_EOL, "finally", PHP_EOL, PHP_EOL;
		});
}

$p3 = WebThread::rpcSend(
	function () {
		sleep(1);
		echo "Ok 3";
	}
);

$responsePromise = await($p3);
echo "await: ", PHP_EOL;
var_export($responsePromise);
echo PHP_EOL, PHP_EOL;

// === TIMED WORK ===
// Executes a lightweight timed task and returns how many times it was run
$count = workWait(function () {
	usleep(1);
});

echo "workWait executed {$count} times", PHP_EOL;
echo "End", PHP_EOL;
