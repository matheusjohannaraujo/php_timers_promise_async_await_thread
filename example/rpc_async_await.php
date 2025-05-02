<?php

// Allows signal handling during script execution (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\RPC;
use function MJohann\Packlib\Functions\{async, await, workWait};

require_once "../vendor/autoload.php";

// Initialize the WebThread system with the RPC endpoint and a secret key
RPC::init("http://localhost:8080/rpc.php", "secret");

echo "Start", PHP_EOL;

// ==============================
// Block 1: Send two RPC tasks concurrently and attach callbacks
// ==============================

$promise1 = RPC::send(
    function () {
        sleep(5);
        echo "Ok 1"; // Printed by the remote process
    }
);

$promise2 = RPC::send(
    function () {
        sleep(3);
        echo "Ok 2"; // Printed by the remote process
    }
);

// Handle both promises with then/catch/finally
foreach ([$promise1, $promise2] as $promise) {
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

// ==============================
// Block 2: Await a single RPC task
// ==============================

$promise3 = RPC::send(
    function () {
        sleep(1);
        echo "Ok 3"; // Printed by the remote process
    }
);

try {
    // Await waits for the remote task to finish and returns its result
    $response = await($promise3);
    echo "await: ", $response, PHP_EOL;
} catch (\Throwable $th) {
    // Handle exceptions if the RPC fails
    echo "catch: ", $th->getMessage(), PHP_EOL;
}

// ==============================
// Block 3: Launch 5 async tasks with callbacks
// ==============================

$maxSleepTime = 3;
$totalTasks = 5;

for ($i = 1; $i <= $totalTasks; $i++) {
    async(function () use ($maxSleepTime) {
        // Simulate a task that takes a random time to complete
        $sleep = rand(1, $maxSleepTime);
        sleep($sleep);
        return $sleep;
    })
        ->then(function ($sleep) use ($i) {
            echo "then: Async task {$i} finished in {$sleep} seconds", PHP_EOL;
        })
        ->catch(function ($error) {
            echo "catch: ", $error, PHP_EOL;
        });
}

// ==============================
// Block 4: Launch 5 async tasks and wait for each (blocking)
// ==============================

$asyncTasks = [];

for ($i = 1; $i <= $totalTasks; $i++) {
    $asyncTasks[] = async(function () use ($maxSleepTime) {
        $sleep = rand(1, $maxSleepTime);
        sleep($sleep);
        return $sleep;
    });
}

foreach ($asyncTasks as $index => $promise) {
    try {
        $sleep = await($promise); // Waits until the task is completed
        $taskNumber = $index + 1;
        echo "await: Async task {$taskNumber} finished in {$sleep} seconds", PHP_EOL;
    } catch (\Throwable $th) {
        echo "catch: ", $th->getMessage(), PHP_EOL;
    }
}

// ==============================
// Block 5: Wait loop for any remaining background workers
// ==============================

// workWait runs a loop while background workers are still active
// The provided callback is called repeatedly to prevent 100% CPU usage
$executionCount = workWait(function () {
    usleep(1); // Sleep briefly in each iteration
});

echo "workWait executed {$executionCount} times", PHP_EOL;
echo "End", PHP_EOL;
