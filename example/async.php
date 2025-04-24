<?php

// Enables signal handling between execution points (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\WebThread;

require_once "../vendor/autoload.php";

// Initialize WebThread with the RPC endpoint and secret key
WebThread::init("http://localhost/rpc.php", "secret");

echo "Start", PHP_EOL;

$maxSleepTime = 2;
$totalTasks = 15;

// === Launch 25 asynchronous tasks ===
for ($taskIndex = 1; $taskIndex <= $totalTasks; $taskIndex++) {
    WebThread::async(function () use ($maxSleepTime) {
        // Random delay between 1 and $maxSleepTime seconds
        $sleepDuration = rand(1, $maxSleepTime);
        sleep($sleepDuration);
        return $sleepDuration;
    })
        ->then(function ($sleepDuration) use ($taskIndex) {
            echo "Async task {$taskIndex} finished in {$sleepDuration} seconds", PHP_EOL;
        });
}

// === Wait for all tasks to complete ===
// Uses a light task to keep the script alive without heavy CPU usage
$loopCount = WebThread::workWait(function () {
    usleep(1); // Yield CPU briefly to avoid 100% usage
});

echo "workWait executed {$loopCount} times", PHP_EOL;
echo "End", PHP_EOL;
