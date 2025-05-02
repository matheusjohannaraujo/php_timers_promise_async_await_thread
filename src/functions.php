<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/zynq
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2025-05-01
*/

namespace MJohann\Packlib\Functions;

use MJohann\Packlib\Timers;
use MJohann\Packlib\Promise;
use MJohann\Packlib\RPC;

if (!function_exists(__NAMESPACE__ . '\setInterval')) {
    /**
     * Repeatedly executes a callback function with a fixed delay between each call.
     *
     * @param callable $call The callback function to execute.
     * @param int $ms Delay in milliseconds between each execution.
     * @param bool $type Optional flag to define execution context (default: true).
     * @return string A unique ID to identify the interval.
     */
    function setInterval(callable $call, int $ms, bool $type = true): string
    {
        return Timers::setInterval($call, $ms, $type);
    }
}

if (!function_exists(__NAMESPACE__ . '\setTimeout')) {
    /**
     * Executes a callback function once after a specified delay.
     *
     * @param callable $call The callback function to execute.
     * @param int $ms Delay in milliseconds before execution.
     * @return string A unique ID to identify the timeout.
     */
    function setTimeout(callable $call, int $ms): string
    {
        return Timers::setTimeout($call, $ms);
    }
}

if (!function_exists(__NAMESPACE__ . '\clearInterval')) {
    /**
     * Clears a previously set interval using its unique ID.
     *
     * @param mixed $uid The unique ID of the interval.
     * @return bool True if cleared successfully, false otherwise.
     */
    function clearInterval($uid): bool
    {
        return Timers::clearInterval($uid);
    }
}

if (!function_exists(__NAMESPACE__ . '\clearTimeout')) {
    /**
     * Clears a previously set timeout using its unique ID.
     *
     * @param mixed $uid The unique ID of the timeout.
     * @return bool True if cleared successfully, false otherwise.
     */
    function clearTimeout($uid): bool
    {
        return Timers::clearTimeout($uid);
    }
}

if (!function_exists(__NAMESPACE__ . '\workRun')) {
    /**
     * Runs pending asynchronous tasks or timers once.
     *
     * @return bool True if any task was executed, false otherwise.
     */
    function workRun(): bool
    {
        return Timers::workRun();
    }
}

if (!function_exists(__NAMESPACE__ . '\workWait')) {
    /**
     * Waits until all scheduled tasks are completed, optionally running a callback.
     *
     * @param callable|null $call Optional callback to execute during waiting.
     * @return int Number of iterations waited.
     */
    function workWait(?callable $call = null): int
    {
        return Timers::workWait($call);
    }
}

if (!function_exists(__NAMESPACE__ . '\async')) {
    /**
     * Starts an asynchronous task and returns a Promise for its result.
     *
     * @param callable $call The task to be executed asynchronously.
     * @return Promise The promise that represents the result of the async task.
     */
    function async(callable $call): Promise
    {
        return RPC::send($call);
    }
}

if (!function_exists(__NAMESPACE__ . '\await')) {
    /**
     * Waits for a Promise to settle and returns the result or throws an exception if rejected.
     *
     * @param Promise $promise The promise to await.
     * @return mixed The resolved value of the promise.
     * @throws \Exception If the promise is rejected.
     */
    function await(Promise $promise): mixed
    {
        $promise->run();
        while ($promise->getMonitor() !== "settled") {
            Timers::workRun();
            usleep(1);
        }
        $result = $promise->getValue();
        if ($promise->getState() === "rejected") {
            if (is_array($result) && isset($result['error'])) {
                $result = $result['error'];
            }
            throw new \Exception(json_encode($result));
        }
        return $result;
    }
}
