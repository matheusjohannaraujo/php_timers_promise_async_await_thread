<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/zynq
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2025-04-23
*/

namespace MJohann\Packlib;

class Promise
{

    private $fun = [];
    private $self = null;
    private $value = null;
    private $state = "pending";
    private $monitor = "undefined";

    public function __construct(?callable $main = null)
    {
        $this->fun["resolved"] = function ($value = null) {
            if ($this->state !== "pending") {
                return;
            }
            $this->state = "resolved";
            $this->value = $value;
        };
        $this->fun["rejected"] = function ($value = null) {
            if ($this->state !== "pending") {
                return;
            }
            $this->state = "rejected";
            $this->value = $value;
        };
        $this->fun["then"] = function () {};
        $this->fun["catch"] = function () {};
        $this->fun["finally"] = function () {};
        if ($main !== null) {
            $main($this->fun["resolved"], $this->fun["rejected"]);
        }
        $this->self = &$this;
    }

    public function then(callable $then, ?callable $catch = null, ?callable $finally = null)
    {
        $this->fun["then"] = &$then;
        if ($catch !== null) {
            $this->fun["catch"] = &$catch;
        }
        if ($finally !== null) {
            $this->fun["finally"] = &$finally;
        }
        $this->run();
        return $this->self;
    }

    public function catch(callable $catch)
    {
        $this->fun["catch"] = &$catch;
        $this->run();
        return $this->self;
    }

    public function finally(callable $finally)
    {
        $this->fun["finally"] = &$finally;
        $this->run();
        return $this->self;
    }

    public function run()
    {
        if ($this->monitor == "undefined") {
            $self = &$this->self;
            $this->monitor = Timers::setInterval(function () use (&$self) {
                if ($self->state !== "pending") {
                    Timers::clearInterval($self->monitor);
                    $self->monitor = "settled";
                    $self->run();
                }
            }, 50);
        } else if ($this->monitor == "settled") {
            if ($this->state == "resolved") {
                $this->fun["then"]($this->value);
                $this->state = "fulfilled";
            }
            if ($this->state == "rejected") {
                $this->fun["catch"]($this->value);
            }
            if ($this->state == "fulfilled" || $this->state == "rejected") {
                $this->fun["finally"]();
            }
        }
        return $this->self;
    }

    public function resolve($value = null)
    {
        $this->fun["resolved"]($value);
    }

    public function reject($value = null)
    {
        $this->fun["rejected"]($value);
    }

    public function cancel(): bool
    {
        $id = $this->monitor;
        $this->monitor = "canceled";
        if (Timers::clearInterval($id)) {
            return true;
        }
        return false;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getMonitor(): string
    {
        return $this->monitor;
    }

    public static function async(callable $main)
    {
        return new Promise(function ($resolve, $reject) use (&$main) {
            WebThread::async(function () use (&$main) {
                $res = function ($val) {
                    echo json_encode(["res", $val]);
                };
                $rej = function ($val) {
                    echo json_encode(["rej", $val]);
                };
                $main($res, $rej);
            })
                ->then(function ($value) use (&$resolve, &$reject) {
                    $value = json_decode($value);
                    if ($value[0] === "res") {
                        return $resolve($value[1]);
                    }
                    $reject($value[1]);
                });
        });
    }

    public static function workWait(?callable $call = null): int
    {
        return Timers::workWait($call);
    }
}
