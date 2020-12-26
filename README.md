
# [Work e Promise](https://github.com/matheusjohannaraujo/php_work_promise)

```php
const DEVELOPER_INFO = [
    "autor" => "Matheus Johann Araújo",
    "country" => "Brasil",
    "state" => "Pernambuco",
    "date" => "2020-12-26"
];
```

## Guia de uso da biblioteca <em>Work e Promise</em> na linguagem PHP

* O termo `callback` significa função passada como parâmetro de uma função, que será chamado por uma função. Em `PHP` os `callbacks` são do tipo `callable` que significa chamável;

* A classe chamada `Closure` é responsável por representar funções anônimas e `arrow functions` (funções de seta);

* Os `callback` normalmente são funções não nomeadas (funções anônimas ou de seta) que são passadas por parâmetro, mas nada impede que uma função nomeada seja passada como parâmetro!

### A biblioteca <em>Work</em> serve para definir funções (callbacks) que devem ser executadas após um determinado tempo, assim como é na linguagem <em>JavaScript</em>

#### <em>Work</em> implementa as funções <em>setInterval, setTimeout, clearInterval e clearTimeout:</em>

* `setInterval(callback, milliseconds)` executa a chamada da função callback no tempo informado de modo infinito. A função retorna um `UID` que pode ser utilizado na função `clearInterval` para remover o `setInterval` da fila de execução;

* `setTimeout(callback, milliseconds)` executa a chamada da função callback no tempo informado de modo único. A função retorna um `UID` que pode ser utilizado na função `clearTimeout` para remover o `setTimeout` da fila de execução;

* `clearInterval(UID)` finaliza a futura execução do `setInterval` que possui o `UID` informado. A função retorna `true` (finalizou) ou `false` (não finalizou ou não encontrou a tarefa agendada);

* `clearTimeout(UID)` finaliza a futura execução do `setTimeout` que possui o `UID` informado. A função retorna `true` (finalizou) ou `false` (não finalizou ou não encontrou a tarefa agendada).

#### Usando a biblioteca Work:

```php
<?php

// EN-US: Include at the beginning of the first file to be interpreted, on the WEB server use TICK sparingly
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado, no servidor WEB use o TICK com moderação
declare(ticks=1);
require_once "work.php";

echo "Inicio\r\n";

$i = 1;

$uid = setInterval(function() use (&$i) {
    echo "Contador " . $i++ . "\r\n";
}, 100);

setTimeout(function() {
    echo "Metade\r\n";
}, 1000);

setTimeout(function() use ($uid) {
    echo "Para Contador\r\n";
    clearInterval($uid);
}, 2000);

echo "Processamento...\r\n";

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun foi executado $count vezes\r\n";

echo "Fim\r\n";
```

#### Observação: A biblioteca Work é compatível com PHP 8 e PHP 7.2 em diante, e serve como uma forma de escalonar o uso do núcleo de processamento, dando a impressão de que a execução do código se encontra em modo "assíncrono", porém tudo ocorre de maneira síncrona.

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
require_once "work.php";
require_once "Promise.php";

echo "Inicio\r\n";

$promise = new Promise(function($resolve, $reject) {
    setTimeout(function() use($resolve) {
        $resolve("ok");
    }, 1000);
    //$reject("error");
});

$promise->then(function($value) {
    echo "then ", $value, "\r\n";
})->catch(function($value) {
    echo "catch ", $value, "\r\n";
})->finally(function(){
    echo "finally\r\n";
});

echo "Processamento...\r\n";
for ($i = 0; $i < 10; $i++) {
    echo "Loop $i\r\n";
    usleep(200000);
}

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
$count = workWait(function() { usleep(1); });
echo "workRun foi executado $count vezes\r\n";

echo "Fim\r\n";
```
