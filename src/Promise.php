<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/zynq
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2025-05-01
*/

namespace MJohann\Packlib;

use function MJohann\Packlib\Functions\{clearInterval, setInterval};

class Promise
{

    private $fun = [];
    private $self = null;
    private $value = null;
    private $state = "pending";
    private $monitor = "undefined";

    /**
     * Initializes a new Promise-like object with optional main function.
     * Sets up default handlers for resolved, rejected, then, catch, and finally.
     *
     * @param callable|null $main Optional function to be called immediately with resolve and reject.
     */
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

    /**
     * Registers a callback to be executed when the promise is resolved.
     * Optional catch and finally callbacks can also be provided.
     *
     * @param callable $then Function to call when resolved.
     * @param callable|null $catch Function to call when rejected.
     * @param callable|null $finally Function to call after resolution or rejection.
     * @return self
     */
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

    /**
     * Registers a callback to be executed if the promise is rejected.
     *
     * @param callable $catch Function to call when rejected.
     * @return self
     */
    public function catch(callable $catch)
    {
        $this->fun["catch"] = &$catch;
        $this->run();
        return $this->self;
    }

    /**
     * Registers a callback to be executed after the promise is either resolved or rejected.
     *
     * @param callable $finally Function to call after completion.
     * @return self
     */
    public function finally(callable $finally)
    {
        $this->fun["finally"] = &$finally;
        $this->run();
        return $this->self;
    }

    /**
     * Internal method that manages the execution flow of the promise.
     * It polls for the promise state and runs the appropriate callbacks.
     *
     * @return self
     */
    public function run()
    {
        if ($this->monitor == "undefined") {
            $self = &$this->self;
            $this->monitor = setInterval(function () use (&$self) {
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

    /**
     * Resolves the promise with an optional value.
     *
     * @param mixed|null $value The value to resolve with.
     */
    public function resolve($value = null)
    {
        $this->fun["resolved"]($value);
    }

    /**
     * Rejects the promise with an optional reason or error value.
     *
     * @param mixed|null $value The reason for rejection.
     */
    public function reject($value = null)
    {
        $this->fun["rejected"]($value);
    }

    /**
     * Cancels the internal polling interval if still active.
     *
     * @return bool True if successfully canceled, false otherwise.
     */
    public function cancel(): bool
    {
        $id = $this->monitor;
        $this->monitor = "canceled";
        if (clearInterval($id)) {
            return true;
        }
        return false;
    }

    /**
     * Retrieves the current resolved or rejected value of the promise.
     *
     * @return mixed|null The value passed to resolve/reject.
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Returns the current state of the promise: pending, resolved, rejected, fulfilled.
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Returns the status of the internal monitor (e.g., interval ID, 'settled', or 'canceled').
     *
     * @return string
     */
    public function getMonitor(): string
    {
        return $this->monitor;
    }
}
