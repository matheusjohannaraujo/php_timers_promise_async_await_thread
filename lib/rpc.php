<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/php_timers_promise_async_await_thread
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2021-09-09
*/

error_reporting(E_ALL);
ini_set("default_charset", "utf-8");
ini_set("set_time_limit", "3600");
ini_set("max_execution_time", "3600");
ini_set("default_socket_timeout", "3600");
ini_set("max_input_time", "3600");
ini_set("max_input_time", "3600");
ini_set("max_input_vars", "6000");
ini_set("memory_limit", "6144M");
ini_set("post_max_size", "6144M");
ini_set("upload_max_filesize", "6144M");
ini_set("max_file_uploads", "200");

require_once "code.php";

$script = $_POST["script"] ?? "";
if (!empty($script)) {
	$script = base64_decode($script);
	$script = rpc_thread_parallel($script);
	$script = base64_encode($script);
}
die($script);

?>
