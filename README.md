
## [Timers, Promise, Async, Await e Thread Parallel (RPC)](https://github.com/matheusjohannaraujo/php_timers_promise_async_await_thread)

### [Guia completo de uso no YouTube](https://www.youtube.com/watch?v=ZFbOnbJQN3U)

```php
const DEVELOPER_INFO = [
    "autor" => "Matheus Johann Araújo",
    "country" => "Brasil",
    "state" => "Pernambuco",
    "date" => "2022-02-02"
];
```

* Compatibilidade comprovada do PHP 7.2 ao 8.0.15

* O termo `callback` significa função passada como parâmetro de uma função, que será chamado por uma função. Em `PHP` os `callbacks` são do tipo `callable` que significa chamável;

* A classe chamada `Closure` é responsável por representar funções anônimas e `arrow functions` (funções de seta);

* Os `callback` normalmente são funções não nomeadas (funções anônimas ou de seta) que são passadas por parâmetro, mas nada impede que uma função nomeada seja passada como parâmetro!

### A biblioteca <em>Timers</em> serve para definir funções (callbacks) que devem ser executadas após um determinado tempo, assim como é na linguagem <em>JavaScript</em>

#### <em>Timers</em> implementa as funções <em>setInterval, setTimeout, clearInterval e clearTimeout:</em>

* `setInterval(callback, milliseconds)` executa a chamada da função callback no tempo informado de modo infinito. A função retorna um `UID` que pode ser utilizado na função `clearInterval` para remover o `setInterval` da fila de execução;

* `setTimeout(callback, milliseconds)` executa a chamada da função callback no tempo informado de modo único. A função retorna um `UID` que pode ser utilizado na função `clearTimeout` para remover o `setTimeout` da fila de execução;

* `clearInterval(UID)` finaliza a futura execução do `setInterval` que possui o `UID` informado. A função retorna `true` (finalizou) ou `false` (não finalizou ou não encontrou a tarefa agendada);

* `clearTimeout(UID)` finaliza a futura execução do `setTimeout` que possui o `UID` informado. A função retorna `true` (finalizou) ou `false` (não finalizou ou não encontrou a tarefa agendada).

#### Usando a biblioteca Timers:

```php
<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

require_once "lib/code.php";

echo "Start", PHP_EOL;

$counter = 1;

$uid = setInterval(function() use (&$counter) {
    echo "Counter: ", $counter++, PHP_EOL;
}, 100);

setTimeout(function() {
    echo "Half of the increments", PHP_EOL;
}, 1000);

setTimeout(function() use ($uid) {
    echo "Stopping the counter", PHP_EOL;
    clearInterval($uid);
}, 2000);

echo "Processing...", PHP_EOL;

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun has been run ${count} times", PHP_EOL;

echo "End", PHP_EOL;
```

#### Observação: A biblioteca Timers permite escalonar o uso do núcleo de processamento, dando a impressão de que a execução do código se encontra em modo "assíncrono", porém tudo ocorre de maneira síncrona.

#### <em>Promise</em> é uma biblioteca que implementa o modelo de funcionamento da <em>Promise</em> em <em>JavaScript</em>.

* `then(callback)` método chamado quando a <em>Promise</em> é resolvida. O `callback` será executado e receberá como parâmetro o valor passado na função `resolve`;

* `catch(callback)` método chamado quando a <em>Promise</em> é rejeitada. O `callback` será executado e receberá como parâmetro o valor passado na função `reject`;

* `finally(callback)` método chamado após uma <em>Promise</em> ser resolvida ou rejeitada, que dispara a execução do `callback` informado como parâmetro.

#### Vejo o exemplo abaixo de como utilizar:

```php
<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);

require_once "lib/code.php";

echo "Start", PHP_EOL;

$promise = new Promise(function($resolve, $reject) {
    $call = rand(0, 1) ? $resolve : $reject;
    setTimeout(function() use($call) {
        $call("message");
    }, 1000);
});

function info_promise() {
    global $promise;
    echo "> monitor: ", $promise->getMonitor(), PHP_EOL;
    echo "> state: ", $promise->getState(), PHP_EOL;
}

info_promise();

$promise->then(function($result) {
    echo "then (${result})", PHP_EOL;
    info_promise();
})->catch(function($error) {
    echo "catch (${error})", PHP_EOL;
    info_promise();
})->finally(function() {
    echo "finally", PHP_EOL;
    info_promise();
});

echo "Processing...", PHP_EOL;
for ($counter = 0; $counter < 10; $counter++) {
    echo "Counter: ${counter}", PHP_EOL;
    usleep(200000);
}

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun has been run ${count} times", PHP_EOL;

echo "End", PHP_EOL;
```
