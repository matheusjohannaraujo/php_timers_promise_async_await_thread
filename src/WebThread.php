<?php

namespace MJohann\Packlib;

use MJohann\Packlib\Facades\CallMorph;

class WebThread
{

    private static string $LOCATION_THREAD_HTTP = "http://localhost/rpc.php";

    public static function init(string $LOCATION_THREAD_HTTP)
    {
        self::$LOCATION_THREAD_HTTP = $LOCATION_THREAD_HTTP;
    }

    public static function threadParallel(
        $script,
        bool $waitResponse = true,
        bool $returnPromise = false,
        bool $infoRequest = true,
        ?string $threadHttp = null
    ) {
        if (is_callable($script)) {
            $script = [$script];
        }

        if (!is_array($script)) {
            $script = [fn() => print("invalid script")];
        }

        $threadHttp ??= self::$LOCATION_THREAD_HTTP;
        CallMorph::init("secret");

        foreach ($script as $key => $value) {
            $script[$key] = CallMorph::serialize($value);
        }

        if (!$waitResponse && !$returnPromise) {
            foreach ($script as $key => $value) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $threadHttp,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => ['script' => base64_encode($value)],
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_FRESH_CONNECT => true,
                    CURLOPT_CONNECTTIMEOUT => 0,
                    CURLOPT_TIMEOUT_MS => 500
                ]);

                $response = json_decode(base64_decode(curl_exec($ch)), true);
                $script[$key] = [
                    "response" => $response ?: null,
                    "await" => false,
                    "error" => curl_errno($ch) ? curl_error($ch) : null,
                    "info" => $infoRequest ? curl_getinfo($ch) : null
                ];
                curl_close($ch);
            }
            return count($script) === 1 ? $script[0] : $script;
        }

        $mch = curl_multi_init();
        foreach ($script as $key => $value) {
            $script[$key] = curl_init();
            curl_setopt_array($script[$key], [
                CURLOPT_URL => $threadHttp,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => ['script' => base64_encode($value)],
            ]);
            curl_multi_add_handle($mch, $script[$key]);
        }

        $resultCurl = function () use (&$mch, &$script, $infoRequest) {
            foreach ($script as $key => $ch) {
                $response = json_decode(base64_decode(curl_multi_getcontent($ch)), true);
                $script[$key] = [
                    "response" => $response,
                    "await" => true,
                    "error" => curl_errno($ch) ? curl_error($ch) : null,
                    "info" => $infoRequest ? curl_getinfo($ch) : null
                ];
                curl_multi_remove_handle($mch, $ch);
                curl_close($ch);
            }
            curl_multi_close($mch);
            if (count($script) === 1) {
                $script = $script[0];
            }
        };

        if ($returnPromise) {
            return new Promise(function ($resolve) use (&$resultCurl, &$mch, &$script) {
                $uid = Timers::setInterval(function () use (&$uid, &$resolve, &$resultCurl, &$mch, &$script) {
                    $active = null;
                    curl_multi_exec($mch, $active);
                    if ($active > 0) return;
                    Timers::clearInterval($uid);
                    $resultCurl();
                    $resolve($script);
                }, 50);
            });
        }

        do {
            $active = null;
            curl_multi_exec($mch, $active);
            usleep(50);
        } while ($active > 0);

        $resultCurl();
        return $script;
    }

    public static function rpcThreadParallel(string $script): string
    {
        if (empty($script)) return json_encode("");

        ob_start();
        $returned = null;
        try {
            CallMorph::init("secret");
            $returned = CallMorph::unserialize($script)();
        } catch (\Throwable $th) {
            var_dump($th);
        }
        $printed = ob_get_clean();

        return json_encode(
            empty($printed) && empty($returned) ? "" : (!empty($printed) && empty($returned) ? $printed : (empty($printed) && !empty($returned) ? $returned :
                ["printed" => $printed, "returned" => $returned]))
        );
    }

    public static function async(callable $call, bool $return = true)
    {
        $parallel = self::threadParallel($call, $return, $return);
        return new Promise(function ($resolve) use (&$parallel, $return) {
            if (!$return) {
                $resolve($parallel["response"]);
            } else {
                $parallel->then(fn($val) => $resolve($val["response"]));
            }
            Timers::workRun();
        });
    }

    public static function await(Promise $promise)
    {
        $promise->run();
        while ($promise->getMonitor() !== "settled") {
            Timers::workRun();
            usleep(1);
        }
        return $promise->getValue();
    }

    public static function rpcProcess(string $script): string
    {
        return base64_encode(
            !empty($script) ? self::rpcThreadParallel(base64_decode($script)) : ""
        );
    }
}
