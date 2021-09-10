<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_timers_promise_async_await_thread
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2021-09-10
--------------------------------------------------------------------------------------------------------
EN-US: Include at the beginning of the first file to be interpreted, do not use TICK on a WEB server
PT-BR: Incluir no início do primeiro arquivo a ser interpretado, não use o TICK em servidor WEB

    declare(ticks=1);
    require_once "work.php";

EN-US: Include after timed calls
PT-BR: Incluir após chamadas programadas (agendadas)
    
    workWait(function() { usleep(1); });
--------------------------------------------------------------------------------------------------------
*/

require_once "vendor/autoload.php";

define("LOCATION_THREAD_HTTP", "http://localhost/php_timers_promise_async_await_thread/lib/rpc.php");
use function Opis\Closure\{serialize as sopis, unserialize as uopis};

/**
 * Código construído com base nos links abaixo.
 *  - https://www.toni-develops.com/2017/09/05/curl-multi-fetch
 *  - https://imasters.com.br/back-end/non-blocking-asynchronous-requests-usando-curlmulti-e-php
 *  - https://www.php.net/manual/pt_BR/function.curl-setopt.php
 *  - https://thiagosantos.com/blog/623/php/php-curl-timeout-e-connecttimeout
 *
 * @param callable|array[callable] $script
 * @param bool $wait_response [optional, default = true]
 * @param bool $return_promise [optional, default = false]
 * @param bool $info_request [optional, default = true]
 * @param string $thread_http [optional, default = null]
 * @return array|Promise
 */
function thread_parallel(
    $script,
    bool $wait_response = true,
    bool $return_promise = false,
    bool $info_request = true,
    ?string $thread_http = null
) {
    if (is_callable($script)) {
        $script = [$script];
    }
    if (!is_array($script)) {
        $script = [function() { echo "invalid script"; }];
    }
    if ($thread_http === null) {
        $thread_http = LOCATION_THREAD_HTTP;
    }
    foreach ($script as $key => $value) {
        $script[$key] = sopis($value);
    }
    if (!$wait_response && !$return_promise) { // Requisição sem espera de resposta
        foreach ($script as $key => $value) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $thread_http);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, [
                "script" => base64_encode($value)
            ]);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
            // Tempo em que o client pode aguardar para conectar no server
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
            // Tempo em que o solicitante espera por uma resposta
            curl_setopt($ch, CURLOPT_TIMEOUT_MS, 500);
            $response = json_decode(base64_decode(curl_exec($ch)), true);
            $script[$key] = [
                "response" => empty($response) ? null : $response,
                "await" => false,
                "error" => curl_errno($ch) ? curl_error($ch) : null,
                "info" => $info_request ? curl_getinfo($ch) : null
            ];
            curl_close($ch);
        }
        if (count($script) === 1) {
            $script = $script[0];
        }
    } else { // Requisição com espera da resposta
        // Inicializa um multi-curl handle
        $mch = curl_multi_init();
        foreach ($script as $key => $value) {
            // Inicializa e seta as opções para cada requisição
            $script[$key] = curl_init();
            curl_setopt($script[$key], CURLOPT_URL, $thread_http);
            curl_setopt($script[$key], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($script[$key], CURLOPT_POSTFIELDS, [
                "script" => base64_encode($value)
            ]);
            // Adiciona a requisição channel ($script[$key]) ao multi-curl handle ($mch)
            curl_multi_add_handle($mch, $script[$key]);
        }
        $result_curl = function () use (&$mch, &$script, &$aes, &$info_request) {
            foreach ($script as $key => $ch) {
                $response = json_decode(base64_decode(curl_multi_getcontent($ch)), true);// Acessa a resposta de cada requisição
                $script[$key] = [
                    "response" => $response,
                    "await" => true,
                    "error" => curl_errno($ch) ? curl_error($ch) : null,
                    "info" => $info_request ? curl_getinfo($ch) : null
                ];
                // Remove o channel ($ch) da requisição do multi-curl handle ($mch)
                curl_multi_remove_handle($mch, $ch);
                // Fecha o channel ($ch)
                curl_close($ch);
            }
            // Fecha o multi-curl handle ($mch)
            curl_multi_close($mch);
            if (count($script) === 1) {
                $script = $script[0];
            }
        };
        if ($return_promise) {
            return new Promise(function($resolve, $reject) use (&$result_curl, &$mch, &$script, &$aes) {
                $uidInterval = setInterval(function() use (&$uidInterval, &$resolve, &$result_curl, &$mch, &$script, &$aes) {
                    $active = null;
                    curl_multi_exec($mch, $active);
                    if ($active > 0) {
                        return;
                    }
                    clearInterval($uidInterval);
                    $result_curl();
                    $resolve($script);
                }, 50);
            });
        }
        // Fica em busy-waiting até que todas as requisições retornem
        do {
            $active = null;
            // Executa as requisições definidas no multi-curl handle, e retorna imediatamente o status das requisições
            curl_multi_exec($mch, $active);
            usleep(50);
        } while($active > 0);
        $result_curl();
    }
    return $script;
}

/**
 *
 * **Function -> rpc_thread_parallel**
 *
 * EN-US: Run the callback and return your result
 *
 * PT-BR: Executa o callback e retorna o seu resultado
 *
 * @param string $script [string to callable]
 * @return string
 */
function rpc_thread_parallel(string $script)
{
    if (!empty($script)) {
        ob_start();
        $returned = null;
        try {
            $returned = uopis($script)();
        } catch (\Throwable $th) {
            var_dump($th);
        }
        $printed = ob_get_clean();
        if (empty($printed) && empty($returned)) {
            $script = "";
        } else if (!empty($printed) && empty($returned)) {
            $script = $printed;
        } else if (empty($printed) && !empty($returned)) {
            $script = $returned;
        } else {
            $script = [
                "printed" => &$printed,
                "returned" => &$returned
            ];
        }
    } else {
        $script = "";
    }
    return json_encode($script);
}

/**
 *
 * **Function -> async**
 *
 * EN-US: Executes a function without blocking the flow of execution
 *
 * PT-BR: Executa uma função sem bloquear o fluxo de execução
 *
 * @param callable $call
 * @param bool $return [optional, default = true]
 * @return Promise
 */
function async(callable $call, bool $return = true)
{
    $parallel = thread_parallel($call, $return, $return);
    return new Promise(function($resolve) use (&$parallel, $return) {
        if (!$return) {
            $resolve($parallel["response"]);
        } else {
            $parallel->then(function($val) use ($resolve) {
                $resolve($val["response"]);
            });
        }
    });
}

/**
 *
 * **Function -> await**
 *
 * EN-US: Waits for a Promise to be resolved and returns the result of the execution
 *
 * PT-BR: Espera que uma Promise seja resolvida e retorna o resultado da execução
 *
 * @param Promise $promise
 * @return mixed
 */
function await(Promise $promise)
{
    $promise->run();
    while ($promise->getMonitor() !== "settled") {
        workRun();
        usleep(1);
    }
    return $promise->getValue();
}

#-------------------------------------------------------------------------------------------------------

$GL_WORK = [];
$GL_WORK_RUN_COUNT = 0;
$GL_TICK = false;
$GL_TICK_EXIST = false;

function initValues() :void
{
    global $GL_WORK;
    global $GL_WORK_RUN_COUNT;
    global $GL_TICK;
    global $GL_TICK_EXIST;
    $GL_WORK = $GL_WORK ?? [];
    $GL_WORK_RUN_COUNT = $GL_WORK_RUN_COUNT ?? 0;
    $GL_TICK = $GL_TICK ?? false;
    $GL_TICK_EXIST = $GL_TICK_EXIST ?? false;
}

function workRun() :bool
{
    initValues();
    global $GL_WORK;
    global $GL_WORK_RUN_COUNT;
    static $secondsTime;
    static $lastTime;
    if (count($GL_WORK) > 0) {
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
    }
    return count($GL_WORK) > 0;
}

function workWait(callable $call = null) :int
{
    initValues();
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
    initValues();
    global $GL_WORK;
    global $GL_TICK;
    global $GL_WORK_RUN_COUNT;
    while (count($GL_WORK) > 0) { $call(); }
    if (is_callable($GL_TICK)) {
        unregister_tick_function($GL_TICK);
    }    
    $GL_WORK = [];
    $GL_TICK = false;
    return $GL_WORK_RUN_COUNT;
}

function setInterval(callable $call, int $ms, bool $type = true) :string
{
    initValues();
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
    if ($GL_TICK === false) {
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
    initValues();
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

#-------------------------------------------------------------------------------------------------------

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

    public function run()
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

    public static function async(callable $main)
    {
        return new Promise(function($resolve, $reject) use (&$main) {
            async(function () use (&$main) {
                $res = function ($val) {
                    echo json_encode(["res", $val]);
                };
                $rej = function ($val) {
                    echo json_encode(["rej", $val]);
                };
                $main($res, $rej);
            })
            ->then(function($value) use (&$resolve, &$reject) {
                $value = json_decode($value);
                if ($value[0] === "res") {
                    return $resolve($value[1]);
                }
                $reject($value[1]);
            });
        });
    }

}
