<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_work_promise
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2021-08-29
*/

class Promise {
    
    private $fun = [];
    private $self = null;
    private $value = null;
    private $state = "pending";
    private $monitor = "undefined";

    public function __construct(callable $main = null)
    {
        $this->fun["resolved"] = function($value = null) {
            if ($this->state !== "pending") {
                return;
            }
            $this->state = "resolved";
            $this->value = $value;
        };
        $this->fun["rejected"] = function($value = null) {
            if ($this->state !== "pending") {
                return;
            }
            $this->state = "rejected";
            $this->value = $value;
        };
        $this->fun["then"] = function() {};
        $this->fun["catch"] = function() {};
        $this->fun["finally"] = function() {};
        if ($main !== null) {
            $main($this->fun["resolved"], $this->fun["rejected"]);
        }
        $this->self = &$this;
    }

    public function then(callable $then, callable $catch = null, callable $finally = null)
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

    private function run()
    {
        if ($this->monitor == "undefined") {
            $self = &$this->self;
            $this->monitor = setInterval(function() use (&$self) {
                if ($self->state !== "pending") {
                    clearInterval($self->monitor);
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

    public function cancel() :bool
    {
        $id = $this->monitor;
        $this->monitor = "canceled";
        if (clearInterval($id)) {
            return true;
        }
        return false;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getState() :string
    {
        return $this->state;
    }

    public function getMonitor() :string
    {
        return $this->monitor;
    }

}
