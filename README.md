# [Zynq](https://github.com/matheusjohannaraujo/zynq)

**Zynq** is a PHP library that brings JavaScript-like asynchronous features to PHP, including support for timers, promises, async/await behavior, and multi-threaded execution via RPC.

## 📦 Installation

Install via [Packagist/Composer](https://packagist.org/packages/mjohann/zynq):

```bash
composer require mjohann/zynq
```

## ⚙️ Requirements

- PHP 8.0 or higher

## 🚀 Features

Zynq currently supports the following features:

- `setTimeout`
- `setInterval`
- `clearTimeout`
- `clearInterval`
- `Promise`
- `then`
- `catch`
- `finally`
- `await`
- `RPC`
- `async`

## 🧪 Usage Examples

> 📺 [Full usage guide (previous version) on YouTube](https://www.youtube.com/watch?v=ZFbOnbJQN3U)

---

### ⏲️ Timers

Zynq provides `setTimeout`, `setInterval`, `clearTimeout`, and `clearInterval`, mirroring their JavaScript counterparts.

#### Key Concepts

- A **callback** is a function passed as a parameter to another function. In PHP, callbacks are typically of type `callable`.
- PHP's `Closure` class is used to represent anonymous and arrow functions.
- Named functions can also be used as callbacks.

#### Functions

- `setInterval(callback, milliseconds)` — Repeatedly executes the given callback every X milliseconds. Returns a unique ID (UID) for managing the interval.
- `setTimeout(callback, milliseconds)` — Executes the callback once after X milliseconds. Returns a UID.
- `clearInterval(UID)` — Stops the scheduled `setInterval` by UID. Returns `true` if successful, `false` otherwise.
- `clearTimeout(UID)` — Stops the scheduled `setTimeout` by UID. Returns `true` if successful, `false` otherwise.

#### Example

```php
<?php

declare(ticks=1);

use function MJohann\Packlib\Functions\{clearInterval, setInterval, setTimeout, workWait};

require_once "vendor/autoload.php";

echo "Start", PHP_EOL;

$counter = 1;

$intervalId = setInterval(function () use (&$counter) {
    echo "Counter: {$counter}", PHP_EOL;
    $counter++;
}, 100);

setTimeout(function () {
    echo "Halfway through", PHP_EOL;
}, 1000);

setTimeout(function () use ($intervalId) {
    echo "Stopping the counter", PHP_EOL;
    clearInterval($intervalId);
}, 2000);

echo "Processing...", PHP_EOL;

$loopCount = workWait(function () {
    usleep(1); // avoid CPU overload
});

echo "workWait was executed {$loopCount} times", PHP_EOL;
echo "End", PHP_EOL;
```

> ℹ️ Zinq enables staggered execution of the main thread through the features provided by Timers. However, blocking actions (such as long-running or synchronous code) can interrupt this staggered execution. True parallelism is only achieved when using the RPC::send feature or its alias async.

---

### 🔁 Promises

Zynq includes a `Promise` class inspired by JavaScript's native Promise implementation.

#### API

- `then(callback)` — Called when the promise is resolved. The callback receives the resolved value.
- `catch(callback)` — Called when the promise is rejected. The callback receives the rejection reason.
- `finally(callback)` — Called when the promise is either resolved or rejected.

#### Example

```php
<?php

declare(ticks=1);

use MJohann\Packlib\Promise;
use function MJohann\Packlib\Functions\{setTimeout, workWait};

require_once "vendor/autoload.php";

echo "Start", PHP_EOL;

$promise = new Promise(function ($resolve, $reject) {
    $callback = rand(0, 1) ? $resolve : $reject;

    setTimeout(function () use ($callback) {
        $callback("message");
    }, 1000);
});

function logPromiseStatus(Promise $promise): void
{
    echo "> Monitor: ", $promise->getMonitor(), PHP_EOL;
    echo "> State: ", $promise->getState(), PHP_EOL;
}

logPromiseStatus($promise);

$promise
    ->then(function ($result) use ($promise) {
        echo "then: ", $result, PHP_EOL;
        logPromiseStatus($promise);
    })
    ->catch(function ($error) use ($promise) {
        echo "catch: ", $error, PHP_EOL;
        logPromiseStatus($promise);
    })
    ->finally(function () use ($promise) {
        echo "finally", PHP_EOL;
        logPromiseStatus($promise);
    });

echo "Processing loop...", PHP_EOL;

for ($i = 0; $i < 10; $i++) {
    echo "Counter: ", $i, PHP_EOL;
    usleep(200000); // 200ms
}

$executions = workWait(function () {
    usleep(1);
});

echo "workWait completed. Timers executed: $executions times", PHP_EOL;
echo "End", PHP_EOL;
```

> 📂 More examples available in the [`example/`](example/) folder.

---

## 📁 Project Structure

```
zynq/
├── src/
│   ├── functions.php
│   ├── Promise.php
│   ├── Timers.php
│   └── RPC.php
├── example/
│   ├── promise.php
│   ├── timers.php
│   ├── rpc_async_await.php
│   ├── rpc.php
│   └── run.bat
├── composer.json
├── .gitignore
├── LICENSE
└── README.md
```

---

## 📄 License

This project is licensed under the [MIT License](LICENSE).

---

## 👨‍💻 Author

Developed by [Matheus Johann Araújo](https://github.com/matheusjohannaraujo) – Pernambuco, Brazil.
