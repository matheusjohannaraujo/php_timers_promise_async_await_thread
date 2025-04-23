<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/zynq
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2025-04-23
*/

namespace MJohann\Packlib;

class Timers
{
    private static array $work = [];
    private static int $workRunCount = 0;
    private static mixed $tick = false;
    private static bool $tickExist = false;

    private static function initValues(): void
    {
        self::$work = self::$work ?? [];
        self::$workRunCount = self::$workRunCount ?? 0;
        self::$tick = self::$tick ?? false;
        self::$tickExist = self::$tickExist ?? false;
    }

    public static function workRun(): bool
    {
        self::initValues();
        static $secondsTime;
        static $lastTime;
        if (count(self::$work) > 0) {
            if ($secondsTime === null && $lastTime === null) {
                $secondsTime = 0 / 1000; // 0ms
                $lastTime = microtime(true);
            } else if (microtime(true) - $lastTime > $secondsTime) {
                self::$workRunCount++;
                $lastTime += $secondsTime;
                $secondsTimeSmaller = null;
                foreach (self::$work as $key => &$work) {
                    $last = &$work["last"];
                    $seconds = &$work["seconds"];
                    if ($secondsTimeSmaller === null) {
                        $secondsTimeSmaller = $seconds;
                    } else if ($secondsTimeSmaller > $seconds) {
                        $secondsTimeSmaller = $seconds;
                    }
                    if (microtime(true) - $last > $seconds) {
                        $work["call"]();
                        if ($work["type"]) {
                            $last += $seconds;
                        } else {
                            unset(self::$work[$key]);
                        }
                    }
                }
                $secondsTime = $secondsTimeSmaller;
            }
        }
        return count(self::$work) > 0;
    }

    public static function workWait(?callable $call = null): int
    {
        self::initValues();
        if ($call === null) {
            $call = function () {
                usleep(1);
            };
        } else if (self::$tickExist) {
            return self::workWaitTick($call);
        }
        while (self::workRun()) {
            $call();
        }
        return self::$workRunCount;
    }

    private static function workWaitTick(callable $call): int
    {
        self::initValues();
        while (count(self::$work) > 0) {
            $call();
        }
        if (is_callable(self::$tick)) {
            unregister_tick_function(self::$tick);
        }
        self::$work = [];
        self::$tick = false;
        return self::$workRunCount;
    }

    public static function setInterval(callable $call, int $ms, bool $type = true): string
    {
        self::initValues();
        $uid = uniqid();
        if ($ms < 0) {
            $ms = 0;
        }
        self::$work[$uid] = [
            "call" => &$call,
            "last" => microtime(true),
            "seconds" => $ms / 1000,
            "type" => &$type
        ];
        if (self::$tick === false) {
            self::$workRunCount = 0;
            self::$tick = function () {
                self::$tickExist = true;
                self::workRun();
            };
            register_tick_function(self::$tick);
        }
        return $uid;
    }

    public static function setTimeout(callable $call, int $ms): string
    {
        return self::setInterval($call, $ms, false);
    }

    public static function clearInterval($uid): bool
    {
        self::initValues();
        if (isset(self::$work[$uid])) {
            unset(self::$work[$uid]);
            return true;
        }
        return false;
    }

    public static function clearTimeout($uid): bool
    {
        return self::clearInterval($uid);
    }
}
