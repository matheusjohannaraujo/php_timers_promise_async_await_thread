<?php

// Allows signal handling during script execution (use sparingly on web servers)
declare(ticks=1);

use MJohann\Packlib\Promise;
use function MJohann\Packlib\Functions\{setTimeout, workWait};

require_once "../vendor/autoload.php";

echo "Start", PHP_EOL;

/**
 * Creates a promise that randomly resolves or rejects after 1 second.
 */
$promise = new Promise(function ($resolve, $reject) {
    $callback = rand(0, 1) ? $resolve : $reject;

    setTimeout(function () use ($callback) {
        $callback("message");
    }, 1000);
});

/**
 * Logs the current status of the promise.
 */
function logPromiseStatus(Promise $promise): void
{
    echo "> Monitor: ", $promise->getMonitor(), PHP_EOL;
    echo "> State: ", $promise->getState(), PHP_EOL;
}

// Initial log of the promise status
logPromiseStatus($promise);

// Set promise handlers
$promise
    ->then(function ($result) use ($promise) {
        echo "then: ", $result, PHP_EOL;
        logPromiseStatus($promise);
    })
    ->catch(function ($error) use ($promise) {
        echo "catch: ", $error, PHP_EOL;
        logPromiseStatus($promise);
    })
    ->finally(function () use ($promise) {
        echo "finally", PHP_EOL;
        logPromiseStatus($promise);
    });

echo "Processing loop...", PHP_EOL;

/**
 * Simulates asynchronous processing with delay
 */
for ($i = 0; $i < 10; $i++) {
    echo "Counter: ", $i, PHP_EOL;
    usleep(200000); // 200ms
}

// Waits for all scheduled promises and timers to complete
$executions = workWait(function () {
    usleep(1); // small delay to allow timer execution
});

echo "workWait completed. Timers executed: $executions times", PHP_EOL;
echo "End", PHP_EOL;
