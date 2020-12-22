<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/makemvcss
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2020-12-22
*/

require_once "tick.php";

class Promise {

    private $val = null;
    private $type = null;
    private $fun = [];
    private $self = null;

    public function __construct($fun = null) {
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
        if (is_callable($fun)) {
            $fun($this->fun["resolve"], $this->fun["rejected"]);
        }
        $this->self = $this;
    }

    private $interval = null;

    public function then($fun) {
        if (is_callable($fun)) {
            $this->fun["then"] = &$fun;
        }
        $this->run();
        return $this->self;
    }

    public function catch($fun) {
        if (is_callable($fun)) {
            $this->fun["catch"] = &$fun;
        }
        $this->run();
        return $this->self;
    }

    public function finally($fun) {
        if (is_callable($fun)) {
            $this->fun["finally"] = &$fun;
        }
        $this->run();
        return $this->self;
    }

    private function run() {
        if ($this->interval === null) {
            $v = &$this->self;
            $this->interval = setInterval(function() use (&$v) {
                if ($v->type !== null) {
                    $uid = $v->interval;                    
                    clearInterval($uid);
                    $v->interval = "closed";
                    $v->run();
                }
            }, 100);
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
