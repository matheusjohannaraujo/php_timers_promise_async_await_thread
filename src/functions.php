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
    function async(callable $call): Promise
    {
        return WebThread::rpcSend($call);
        /*return new Promise(function ($resolve, $reject) use (&$call) {
            
            $promise->then(function ($resolve, $in) use (&$resolve) {
                $resolve($result['response']);
            });
            $promise->catch(function ($result) use (&$reject) {
                $reject($result['error']);
            });
        });*/
    }
}

if (!function_exists(__NAMESPACE__ . '\await')) {
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
