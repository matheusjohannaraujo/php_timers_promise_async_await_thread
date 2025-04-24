<?php

// Use TICK to allow signal handling during script execution (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\Promise;
use MJohann\Packlib\WebThread;

require_once "../vendor/autoload.php";

// Initialize WebThread with the RPC endpoint and secret key
WebThread::init("http://localhost/rpc.php", "secret");

echo "Start", PHP_EOL;

// === Create 15 asynchronous promises ===
for ($index = 1; $index <= 15; $index++) {
    Promise::async(function ($resolve, $reject) {
        // Simulate a delay of 0 to 2 seconds
        $sleepTime = rand(0, 2);
        sleep($sleepTime);

        // Randomly resolve or reject the promise
        (rand(0, 1) ? $resolve : $reject)($sleepTime);
    })
        ->then(function ($sleepTime) use ($index) {
            echo "Promise {$index} resolved after {$sleepTime} seconds", PHP_EOL;
        })
        ->catch(function ($sleepTime) use ($index) {
            echo "Promise {$index} rejected after {$sleepTime} seconds", PHP_EOL;
        });
}

// === Keep the script alive until all promises complete ===
// The loop waits using a tiny task to prevent CPU spinning
$workLoopCount = Promise::workWait(function () {
    usleep(1); // Prevent 100% CPU usage
});

echo "workWait executed {$workLoopCount} times", PHP_EOL;
echo "End", PHP_EOL;
