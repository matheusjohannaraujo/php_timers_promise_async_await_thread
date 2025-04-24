@echo off
cls
echo.
echo C:\xampp\htdocs\php_timers_promise_async_await_thread\example
echo.
echo -----------------------------
echo php -S localhost:80
echo -----------------------------
echo.
start cmd /c "php -S localhost:80"
echo.
echo -----------------------------
echo timers.php
echo -----------------------------
echo.
php timers.php
echo.
echo -----------------------------
echo promise.php
echo -----------------------------
echo.
php promise.php
echo.
echo -----------------------------
echo promise_async.php
echo -----------------------------
echo.
php promise_async.php
echo.
echo -----------------------------
echo async.php
echo -----------------------------
echo.
php async.php
echo.
echo -----------------------------
echo async_await.php
echo -----------------------------
echo.
php async_await.php
echo.
echo -----------------------------
echo thread.php
echo -----------------------------
echo.
php thread.php
echo.
pause
