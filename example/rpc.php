<?php

declare(ticks=1);

use MJohann\Packlib\WebThread;

require_once "../vendor/autoload.php";

error_reporting(E_ALL);
ini_set("default_charset", "utf-8");
ini_set("set_time_limit", "600");
ini_set("max_execution_time", "600");
ini_set("default_socket_timeout", "600");
ini_set("max_input_time", "600");
ini_set("max_input_time", "600");
ini_set("max_input_vars", "600");
ini_set("memory_limit", "512M");
ini_set("post_max_size", "512M");
ini_set("upload_max_filesize", "512M");
ini_set("max_file_uploads", "200");

die(WebThread::rpcProcess($_POST["script"] ?? ""));
