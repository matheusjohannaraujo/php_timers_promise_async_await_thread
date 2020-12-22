<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-22
*/

/*

Include at the beginning of the first file to be interpreted
    
    declare(ticks=1);

Include after timed calls

    while (count($GL_TICKS)) usleep(1);

*/

$GL_TICKS = [];

function setInterval(callable $callback, int $ms, bool $interval = true, int $max = 0) :string
{
    global $GL_TICKS;
    $uid = uniqid();
    $GL_TICKS[$uid] = true;
    $last = microtime(true);
    $seconds = $ms / 1000;
    register_tick_function(function() use (&$last, $callback, $seconds, $max, $uid, $interval) {
        global $GL_TICKS;
        static $busy = false;
        static $n = 0;
        if ($busy) return;
        $busy = true;
        $now = microtime(true);
        while ($now - $last > $seconds) {
            if (($max && $n == $max) || !isset($GL_TICKS[$uid])) break;
            ++$n;
            $last += $seconds;
            $callback();
            if (!$interval && isset($GL_TICKS[$uid])) {
                unset($GL_TICKS[$uid]);
            }
        }
        $busy = false;
    });
    return $uid;
}

function setTimeout(callable $callback, int $ms) :string
{
    return setInterval($callback, $ms, false, 1);
}

function clearInterval(string $uid) :bool
{
    global $GL_TICKS;
    if (isset($GL_TICKS[$uid])) {
        unset($GL_TICKS[$uid]);
        return true;
    }
    return false;
}

function clearTimeout(string $uid) :bool
{
    return clearInterval($uid);
}
