<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_work_promise
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2021-09-04
----------------------------------------------------------------------------------------------------
EN-US: Include at the beginning of the first file to be interpreted, do not use TICK on a WEB server
PT-BR: Incluir no início do primeiro arquivo a ser interpretado, não use o TICK em servidor WEB

    declare(ticks=1);
    require_once "work.php";

EN-US: Include after timed calls
PT-BR: Incluir após chamadas programadas (agendadas)
    
    workWait(function() { usleep(1); });

*/

$GL_WORK = [];
$GL_WORK_RUN_COUNT = 0;
$GL_TICK = null;
$GL_TICK_EXIST = false;

function workRun() :bool
{
    global $GL_WORK;
    global $GL_WORK_RUN_COUNT;
    static $secondsTime;
    static $lastTime;
    if ($secondsTime === null && $lastTime === null) {
        $secondsTime = 0 / 1000;// 0ms
        $lastTime = microtime(true);
    } else if (microtime(true) - $lastTime > $secondsTime) {
        $GL_WORK_RUN_COUNT++;
        $lastTime += $secondsTime;
        $secondsTimeSmaller = null;
        foreach ($GL_WORK as $key => &$work) {
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
                    unset($GL_WORK[$key]);
                }
            }
        }
        $secondsTime = $secondsTimeSmaller;
    }
    return count($GL_WORK) > 0;
}

function workWait(callable $call = null) :int
{
    global $GL_TICK_EXIST;
    global $GL_WORK_RUN_COUNT;
    if ($call === null) {
        $call = function() { usleep(1); };
    } else if ($GL_TICK_EXIST) {
        return workWaitTick($call);
    }
    while (workRun()) { $call(); }
    return $GL_WORK_RUN_COUNT;
}

function workWaitTick(callable $call) :int
{
    global $GL_WORK;
    global $GL_TICK;
    global $GL_WORK_RUN_COUNT;
    while (count($GL_WORK) > 0) { $call(); }
    unregister_tick_function($GL_TICK);
    $GL_WORK = [];
    $GL_TICK = null;
    return $GL_WORK_RUN_COUNT;
}

function setInterval(callable $call, int $ms, bool $type = true) :string
{
    global $GL_WORK;
    global $GL_TICK;
    global $GL_WORK_RUN_COUNT;
    $uid = uniqid();
    if ($ms < 0) {
        $ms = 0;
    }
    $GL_WORK[$uid] = [
        "call" => &$call,
        "last" => microtime(true),
        "seconds" => $ms / 1000,
        "type" => &$type
    ];
    if ($GL_TICK === null) {
        $GL_WORK_RUN_COUNT = 0;
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
    global $GL_WORK;
    if (isset($GL_WORK[$uid])) {
        unset($GL_WORK[$uid]);
        return true;
    }
    return false;
}

function clearTimeout($uid) :bool
{
    return clearInterval($uid);
}
