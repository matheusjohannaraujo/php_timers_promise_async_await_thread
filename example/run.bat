@echo off
cls
echo.
echo C:\xampp\htdocs\php_timers_promise_async_await_thread\example
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
echo php -S localhost:8080 (Attention: Single Thread)
echo -----------------------------
echo.
start cmd /c "php -S localhost:8080"
timeout /t 2
echo.
echo -----------------------------
echo thread_async_await.php
echo -----------------------------
echo.
php thread_async_await.php
echo.
echo.
pause
