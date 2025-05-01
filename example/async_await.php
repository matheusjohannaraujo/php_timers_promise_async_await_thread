<?php

// Allows signal handling during script execution (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\WebThread;
use function MJohann\Packlib\Functions\{async, await, workWait};

require_once "../vendor/autoload.php";

// Initialize WebThread with the RPC endpoint and secret key
WebThread::init("http://localhost:8080/rpc.php", "secret");

echo "Start", PHP_EOL;

// First block of code
$maxSleepTime = 3;
$totalTasks = 10;

// Launch 10 asynchronous tasks using 'then' and 'catch' ===
for ($taskIndex = 1; $taskIndex <= $totalTasks; $taskIndex++) {
    async(function () use ($maxSleepTime) {
        // Simulate a task with a random delay (1 to $maxSleepTime seconds)
        $sleepDuration = rand(1, $maxSleepTime);
        sleep($sleepDuration);
        return $sleepDuration;
    })
        ->then(function ($sleepDuration) use ($taskIndex) {
            // Callback executed when the async task completes successfully
            echo "then: Async task {$taskIndex} finished in {$sleepDuration} seconds", PHP_EOL;
        })
        ->catch(function ($error) {
            // Callback executed if the async task throws an exception
            echo "catch: ", $error, PHP_EOL;
        });
}

// Second block of code
// Prepare a new batch of async tasks to be awaited explicitly
$asyncTasks = [];

for ($taskNumber = 1; $taskNumber <= $totalTasks; $taskNumber++) {
    $asyncTasks[] = async(function () use ($maxSleepTime) {
        // Simulate a task with a random delay
        $sleepTime = rand(1, $maxSleepTime);
        sleep($sleepTime);
        return $sleepTime;
    });
}

// Wait for each async task to complete using await() ===
foreach ($asyncTasks as $index => $promise) {
    try {
        $taskIndex = $index + 1;
        $sleepTime = await($promise); // Blocks until the async task is done
        echo "await: Async task {$taskIndex} finished in {$sleepTime} seconds", PHP_EOL;
    } catch (\Throwable $th) {
        // Handle exceptions thrown by any of the tasks
        echo "catch: ", $th->getMessage(), PHP_EOL;
    }
}

// Keep script running to allow background workers to complete ===
$executionCount = workWait(function () {
    // Minimal delay to prevent 100% CPU usage while waiting
    usleep(1);
});

echo "workWait executed {$executionCount} times", PHP_EOL;
echo "End", PHP_EOL;
