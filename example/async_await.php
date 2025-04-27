<?php

// Allows signal handling during script execution (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\WebThread;
use function MJohann\Packlib\Functions\{async, await, workWait};

require_once "../vendor/autoload.php";

// Initialize WebThread with RPC endpoint and secret key
WebThread::init("http://localhost/rpc.php", "secret");

echo "Start", PHP_EOL;

$maxSleepTime = 2;
$totalTasks = 10;
$asyncTasks = [];

// === Launch asynchronous tasks ===
for ($taskNumber = 1; $taskNumber <= $totalTasks; $taskNumber++) {
    $asyncTasks[] = async(function () use ($maxSleepTime) {
        $sleepTime = rand(2, $maxSleepTime);
        sleep($sleepTime);
        return $sleepTime;
    });
}

// === Await and print results of async tasks ===
foreach ($asyncTasks as $index => $promise) {
    $taskNumber = $index + 1;
    $sleepTime = await($promise);
    echo "Await in Async task {$taskNumber} finished in {$sleepTime} seconds", PHP_EOL;
}

// === Keep the script alive for pending executions ===
$executionCount = workWait(function () {
    usleep(1); // Yield CPU briefly to avoid 100% usage
});

echo "workWait executed {$executionCount} times", PHP_EOL;
echo "End", PHP_EOL;
