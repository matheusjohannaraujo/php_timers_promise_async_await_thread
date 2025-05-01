<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/zynq
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2025-05-01
*/

namespace MJohann\Packlib;

class Timers
{
    private static array $work = [];
    private static int $workRunCount = 0;
    private static mixed $tick = false;
    private static bool $tickExist = false;

    /**
     * Initializes internal static variables if not already set.
     *
     * @return void
     */
    private static function initValues(): void
    {
        self::$work = self::$work ?? [];
        self::$workRunCount = self::$workRunCount ?? 0;
        self::$tick = self::$tick ?? false;
        self::$tickExist = self::$tickExist ?? false;
    }

    /**
     * Executes scheduled timers if their delay has passed.
     *
     * @return bool Returns true if any timers are still active, false otherwise.
     */
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

    /**
     * Waits until all scheduled timers finish executing, calling a given function in the loop.
     *
     * @param callable|null $call A function to be called repeatedly while waiting (default: `usleep(1)`).
     * @return int The number of times timers were executed.
     */
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

    /**
     * Internal helper for waiting in environments using register_tick_function.
     *
     * @param callable $call A function to call while waiting.
     * @return int The number of times timers were executed.
     */
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

    /**
     * Registers a recurring function to run at the specified interval (like setInterval in JavaScript).
     *
     * @param callable $call The function to call at each interval.
     * @param int $ms Interval time in milliseconds.
     * @param bool $type True to keep repeating, false for single execution.
     * @return string A unique identifier for the timer.
     */
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

    /**
     * Registers a one-time delayed function call (like setTimeout in JavaScript).
     *
     * @param callable $call The function to call once after the delay.
     * @param int $ms Delay time in milliseconds.
     * @return string A unique identifier for the timer.
     */
    public static function setTimeout(callable $call, int $ms): string
    {
        return self::setInterval($call, $ms, false);
    }

    /**
     * Clears a timer created with setInterval or setTimeout.
     *
     * @param string $uid The unique identifier of the timer.
     * @return bool True if the timer was found and removed, false otherwise.
     */
    public static function clearInterval($uid): bool
    {
        self::initValues();
        if (isset(self::$work[$uid])) {
            unset(self::$work[$uid]);
            return true;
        }
        return false;
    }

    /**
     * Alias for clearInterval, used to clear a timer set with setTimeout.
     *
     * @param string $uid The unique identifier of the timer.
     * @return bool True if the timer was found and removed, false otherwise.
     */
    public static function clearTimeout($uid): bool
    {
        return self::clearInterval($uid);
    }
}
