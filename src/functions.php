<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/zynq
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2025-04-27
*/

namespace MJohann\Packlib\Functions;

use MJohann\Packlib\Timers;
use MJohann\Packlib\Promise;
use MJohann\Packlib\WebThread;

if (!function_exists(__NAMESPACE__ . '\setInterval')) {
    function setInterval(callable $call, int $ms, bool $type = true): string
    {
        return Timers::setInterval($call, $ms, $type);
    }
}

if (!function_exists(__NAMESPACE__ . '\setTimeout')) {
    function setTimeout(callable $call, int $ms): string
    {
        return Timers::setTimeout($call, $ms);
    }
}

if (!function_exists(__NAMESPACE__ . '\clearInterval')) {
    function clearInterval($uid): bool
    {
        return Timers::clearInterval($uid);
    }
}

if (!function_exists(__NAMESPACE__ . '\clearTimeout')) {
    function clearTimeout($uid): bool
    {
        return Timers::clearTimeout($uid);
    }
}

if (!function_exists(__NAMESPACE__ . '\workRun')) {
    function workRun(): bool
    {
        return Timers::workRun();
    }
}

if (!function_exists(__NAMESPACE__ . '\workWait')) {
    function workWait(?callable $call = null): int
    {
        return Timers::workWait($call);
    }
}

if (!function_exists(__NAMESPACE__ . '\async')) {
    function async(callable $call, bool $return = true)
    {
        $parallel = WebThread::threadParallel($call, $return, $return);
        return new Promise(function ($resolve) use (&$parallel, $return) {
            if (!$return) {
                $resolve($parallel["response"]);
            } else {
                $parallel->then(fn($val) => $resolve($val["response"]));
            }
            Timers::workRun();
        });
    }
}

if (!function_exists(__NAMESPACE__ . '\await')) {
    function await(Promise $promise)
    {
        $promise->run();
        while ($promise->getMonitor() !== "settled") {
            Timers::workRun();
            usleep(1);
        }
        return $promise->getValue();
    }
}
