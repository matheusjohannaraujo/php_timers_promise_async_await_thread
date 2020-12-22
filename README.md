
# [Tick, Work e Promise](https://github.com/matheusjohannaraujo/data_manager)

```php
// DEVELOPER INFO
const DEV = [
    "autor" => "Matheus Johann Araújo",
    "country" => "Brasil",
    "state" => "Pernambuco",
    "date" => "2020-12-22"
];
```

## Guia de uso das bibliotecas <em>Tick, Work e Promise</em> no linguagem PHP

* O termo `callback` significa função passada como parâmetro de uma função, em `PHP` os `callback` são do tipo `callable` (chamável). Uma classe chamada `Closure` é responsável por representar funções anônimas e `arrow functions` (funções de seta).

### As bibliotecas Tick e Work servem para definir funções (callback) que devem ser executados em um determinado tempo.

#### <em>Tick e Work</em> implementam as funções <em>setInterval, setTimeout, clearInterval e clearTimeout:</em>

* `setInterval(callback, milliseconds)` executa a chamada da função callback no tempo informado de modo infinito. A função retorna um `UID` que pode ser utilizado na função `clearInterval`, finalizando o loop do `setInterval`;

* `setTimeout(callback, milliseconds)` executa a chamada da função callback no tempo informado de modo único. A função retorna um `UID` que pode ser utilizado na função `clearTimeout`, finalizando o loop do `setTimeout`;

* `clearInterval(UID)` finaliza a futura execução do `setInterval` que possui o `UID` informado. A função retorna `true` (finalizou) ou `false` (não finalizou ou não encontrou a tarefa agendada);

* `clearTimeout(UID)` finaliza a futura execução do `setTimeout` que possui o `UID` informado. A função retorna `true` (finalizou) ou `false` (não finalizou ou não encontrou a tarefa agendada).

#### Observação: A biblioteca Work e Tick servem como uma forma de escalonar o uso do núcleo de processamento, dando a impressão de execução de código de modo "assíncrono", porém de maneira síncrona. É compatível com PHP 8 e PHP 7.

#### Para usar a biblioteca Tick ou Work é necessário seguir as informações baixo:

#### Usando o TICK

```php
// EN-US: Include at the beginning of the first file to be interpreted
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado
declare(ticks=1);
require_once "tick.php";

echo "Inicio\r\n";

$uid = setInterval(function(){
    echo "Interval\r\n";
}, 250);

setTimeout(function() use ($uid) {
    echo "Timeout\r\n";
    clearInterval($uid);
}, 2000);

echo "Processamento...\r\n";

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
while (count($GL_TICKS)) { usleep(1); }

echo "Fim\r\n";
```

#### Usando o WORK

```php
// EN-US: Include at the beginning of the first file to be interpreted
// PT-BR: Incluir no início do primeiro arquivo a ser interpretado
require_once "work.php";

echo "Inicio\r\n";

$uid = setInterval(function(){
    echo "Interval\r\n";
}, 250);

setTimeout(function() use ($uid) {
    echo "Timeout\r\n";
    clearInterval($uid);
}, 2000);

echo "Processamento...\r\n";

// EN-US: Include after timed calls
// PT-BR: Incluir após chamadas programadas (agendadas)
workWait();

echo "Fim\r\n";
```

#### <em>Promise</em> é uma biblioteca que implementa a idéia de funcionamento da <em>Promise</em> em <em>JavaScript</em>. Vejo o exemplo abaixo de como utilizar:

```php
$p = new Promise(function($resolve, $reject) {
    setTimeout(function() use($resolve) {
        $resolve("ok");
    }, 1000);
    //$reject("error");
});

$p->then(function($v) {
    echo "then ", $v, "\r\n";
})->catch(function($v) {
    echo "catch ", $v, "\r\n";
})->finally(function(){
    echo "finally\r\n";
});
```

#### Foram feitos dois exemplos de uso de <em>Promise</em>, o primeiro utilizando o [<em>Tick</em>](./tick-promise/example.php) e outro através do [<em>Work</em>](./work-promise/example.php).

* `then(callback)` método chamado se uma <em>Promise</em> for resolvida. O `callback` será executado e receberá como parâmetro o valor passado na função `resolve`;

* `catch(callback)` método chamado se uma <em>Promise</em> for rejeitada. O `callback` será executado e receberá como parâmetro o valor passado na função `reject`;

* `finally(callback)` método chamado após uma <em>Promise</em> ser resolvida ou rejeitada, que dispara a execução do `callback` informado.