<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_work_promise
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-23
*/

/*

EN-US: Include at the beginning of the first file to be interpreted, do not use TICK on a WEB server
PT-BR: Incluir no início do primeiro arquivo a ser interpretado, não use o TICK em servidor WEB

    declare(ticks=1);
    require_once "work.php";

EN-US: Include after timed calls
PT-BR: Incluir após chamadas programadas (agendadas)
    
    workWait(function() { usleep(1); });

*/

$GL_WORKS = [];
$GL_TICK = null;
$GL_TICK_EXIST = false;
$GL_CALL_COUNT_WORK_RUN = 0;

function workRun() :bool
{
    global $GL_WORKS;
    global $GL_CALL_COUNT_WORK_RUN;
    $GL_CALL_COUNT_WORK_RUN++;
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
    return count($GL_WORKS) > 0;
}

function workWait(callable $call) :int
{
    global $GL_TICK_EXIST;
    global $GL_CALL_COUNT_WORK_RUN;
    if ($GL_TICK_EXIST) {
        return workWaitTick($call);
    }
    while (workRun()) { $call(); }
    return $GL_CALL_COUNT_WORK_RUN;
}

function workWaitTick(callable $call) :int
{
    global $GL_WORKS;
    global $GL_TICK;
    global $GL_CALL_COUNT_WORK_RUN;
    while (count($GL_WORKS) > 0) { $call(); }
    unregister_tick_function($GL_TICK);
    $GL_WORKS = [];
    $GL_TICK = null;
    return $GL_CALL_COUNT_WORK_RUN;
}

function setInterval(callable $call, int $ms, bool $type = true) :string
{
    global $GL_WORKS;
    global $GL_TICK;
    global $GL_CALL_COUNT_WORK_RUN;
    $uid = uniqid();
    $GL_WORKS[$uid] = [
        "call" => &$call,
        "last" => microtime(true),
        "seconds" => $ms / 1000,
        "type" => &$type
    ];
    if ($GL_TICK === null) {
        $GL_CALL_COUNT_WORK_RUN = 0;
        $GL_TICK = function () {
            global $GL_TICK_EXIST;
            $GL_TICK_EXIST = true;
            workRun();
        };
        register_tick_function($GL_TICK);
    }    
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
