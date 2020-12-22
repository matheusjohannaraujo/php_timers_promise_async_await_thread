<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-22
*/

/*

Include after timed calls

    workWait();

*/

$GL_WORKS = [];

function workRun() :bool
{
    global $GL_WORKS;
    foreach ($GL_WORKS as $key => &$work) {
        $last = &$work["last"];
        $seconds = &$work["seconds"];
        $now = microtime(true);
        if ($now - $last > $seconds) {
            $work["call"]();
            if ($work["type"]) {
                $last += $seconds;
            } else {
                unset($GL_WORKS[$key]);
            }
        }
    }
    usleep(1);
    return count($GL_WORKS) > 0;
}

function workWait(callable $call = null)
{
    while (workRun()) {
        if ($call !== null) {
            $call();
        }
    }
}

function setInterval(callable $call, int $ms, bool $type = true) :string
{
    global $GL_WORKS;
    $uid = uniqid();
    $GL_WORKS[$uid] = [
        "call" => &$call,
        "last" => microtime(true),
        "seconds" => $ms / 1000,
        "type" => &$type
    ];
    return $uid;
}

function setTimeout(callable $call, int $ms) :string
{
    return setInterval($call, $ms, false);
}

function clearInterval($uid) :bool
{
    global $GL_WORKS;
    if (isset($GL_WORKS[$uid])) {
        unset($GL_WORKS[$uid]);
        return true;
    }
    return false;
}

function clearTimeout($uid) :bool
{
    return clearInterval($uid);
}
