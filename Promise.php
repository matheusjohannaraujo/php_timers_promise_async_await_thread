<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_work_promise
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-26
*/

class Promise {

    private $val = null;
    private $type = null;
    private $fun = [];
    private $self = null;

    public function __construct(callable $main) {
        $this->fun["resolve"] = function($val = null) {
            if ($this->type !== null) {
                return;
            }
            $this->type = "resolve";
            $this->val = $val;
        };
        $this->fun["rejected"] = function($val = null) {
            if ($this->type !== null) {
                return;
            }
            $this->type = "rejected";
            $this->val = $val;
        };
        $this->fun["then"] = function() {};
        $this->fun["catch"] = function() {};
        $this->fun["finally"] = function() {};
        $main($this->fun["resolve"], $this->fun["rejected"]);
        $this->self = $this;
    }

    private $interval = null;

    public function then(callable $then, callable $catch = null, callable $finally = null) {
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

    public function catch(callable $catch) {
        $this->fun["catch"] = &$catch;
        $this->run();
        return $this->self;
    }

    public function finally(callable $finally) {
        $this->fun["finally"] = &$finally;
        $this->run();
        return $this->self;
    }

    private function run() {
        if ($this->interval === null) {
            $v = &$this->self;
            $this->interval = setInterval(function() use (&$v) {
                if ($v->type !== null) {
                    clearInterval($v->interval);
                    $v->interval = "closed";
                    $v->run();
                }
            }, 50);
        }
        if ($this->interval == "closed") {
            if ($this->type == "resolve") {
                $this->fun["then"]($this->val);
                $this->type = "fulfilled";
            }
            if ($this->type == "rejected") {
                $this->fun["catch"]($this->val);
                $this->type = "fulfilled";
            }
            if ($this->type == "fulfilled") {
                $this->fun["finally"]();
            }
        }        
        return $this->self;
    }

}
