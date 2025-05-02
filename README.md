# [Zynq](https://github.com/matheusjohannaraujo/zynq)

**Zynq** is a PHP library that brings JavaScript-like asynchronous features to PHP, including support for timers, promises, async/await behavior, and multi-threaded execution via RPC.

## 📦 Installation

Install via [Packagist/Composer](https://packagist.org/packages/mjohann/zynq):

```bash
composer require mjohann/zynq
```

---

## ⚙️ Requirements

- PHP 8.0 or higher

---

## 🚀 Features

**Zynq** is a PHP library that brings JavaScript-like asynchronous features to PHP, enabling a smoother and more efficient programming experience. The currently supported features include:

---

### ⏱️ Timers

* **`setTimeout(callback, delay)`**: Executes a function after a specified delay.
* **`setInterval(callback, interval)`**: Repeatedly executes a function at fixed intervals.
* **`clearTimeout(timerId)`**: Cancels a timer previously created with `setTimeout`.
* **`clearInterval(timerId)`**: Cancels a timer previously created with `setInterval`.

### 🔄 Promises & Flow Control

* **`Promise`**: Native-like implementation for handling asynchronous operations.
* **`then(onFulfilled)`**: Defines what to do when a promise is fulfilled.
* **`catch(onRejected)`**: Handles errors when a promise is rejected.
* **`finally(onFinally)`**: Executes code after a promise is settled, regardless of the outcome.
* **`await`**: Waits for a promise to resolve before continuing execution.
* **`async`**: Declares an asynchronous function that returns a promise.

### 🧠 Asynchronous Execution & RPC

* **`RPC` (Remote Procedure Call)**: Allows executing functions in separate processes using a lightweight RPC mechanism.
* **Multi-process Execution**: Simulates multithreading using child processes to improve performance in asynchronous and intensive tasks.

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

workWait(function () { usleep(1); });
```

> ℹ️ Zinq enables staggered execution of the main thread through the features provided by Timers. However, blocking actions (such as long-running or synchronous code) can interrupt this staggered execution. True parallelism is only achieved when using the RPC::send feature or its alias async.

---

### 🔄 Promises & Flow Control

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

$promise
    ->then(function ($result) {
        echo "then: ", $result, PHP_EOL;
    })
    ->catch(function ($error) {
        echo "catch: ", $error, PHP_EOL;
    })
    ->finally(function () {
        echo "finally", PHP_EOL;
    });

echo "Processing loop...", PHP_EOL;

for ($i = 0; $i < 10; $i++) {
    echo "Counter: ", $i, PHP_EOL;
    usleep(200000); // 200ms
}

workWait(function () { usleep(1); });
```

## 📡 RPC (Remote Procedure Call)

Zynq provides a lightweight RPC system that allows you to register and call functions asynchronously in isolated processes. This is useful for offloading heavy computations or running code in parallel without blocking the main execution thread.

### 🔧 How It Works

* **Function Registration**: You can register named functions to be called remotely.
* **Asynchronous Execution**: Registered functions are invoked asynchronously and return a `Promise`.
* **Process Isolation**: Each call runs in a separate process, ensuring non-blocking execution and memory isolation.

### 🧪 Example Usage

```php

<?php

declare(ticks=1);

use MJohann\Packlib\RPC;
use function MJohann\Packlib\Functions\{await, workWait};

require_once "vendor/autoload.php";

// Initialize the WebThread system with the RPC endpoint and a secret key
RPC::init("http://localhost:8080/rpc.php", "secret");

// Send a remote function
$promise = RPC::send(
    function () {
        sleep(2);
        return 5 + 3;
    }
);

// Call the registered function asynchronously
$result = await($promise);
echo "Sum: ", $result, PHP_EOL; // Sum: 8

workWait(function () { usleep(1); });
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
