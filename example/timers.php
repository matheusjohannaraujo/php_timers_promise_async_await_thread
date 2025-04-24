<?php

// Use TICK to allow PHP to handle signal events during execution (use carefully in web servers)
declare(ticks=1);

use MJohann\Packlib\Timers;

require_once "../vendor/autoload.php";

echo "Start", PHP_EOL;

// === Initialize the counter ===
$counter = 1;

// === Set up a recurring timer (interval) ===
// This will run every 100ms, incrementing and printing the counter
$intervalId = Timers::setInterval(function () use (&$counter) {
    echo "Counter: {$counter}", PHP_EOL;
    $counter++;
}, 100);

// === Schedule a timeout at 1000ms ===
// Executes once after 1 second
Timers::setTimeout(function () {
    echo "Half of the increments", PHP_EOL;
}, 1000);

// === Schedule another timeout at 2000ms ===
// Stops the interval after 2 seconds
Timers::setTimeout(function () use ($intervalId) {
    echo "Stopping the counter", PHP_EOL;
    Timers::clearInterval($intervalId);
}, 2000);

echo "Processing...", PHP_EOL;

// === Wait loop to keep the script alive while timers run ===
// Repeatedly executes a tiny task while waiting
$loopCount = Timers::workWait(function () {
    usleep(1); // Prevents CPU from spinning at 100%
});

echo "workWait was executed {$loopCount} times", PHP_EOL;
echo "End", PHP_EOL;
