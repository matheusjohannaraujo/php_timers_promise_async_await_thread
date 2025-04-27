<?php

namespace MJohann\Packlib;

use MJohann\Packlib\CallMorph;
use MJohann\Packlib\SimpleAES256;

class WebThread
{

    private static string $LOCATION_THREAD_HTTP = "http://localhost/rpc.php";
    private static string $SECRET_KEY = "secret";

    public static function init(string $LOCATION_THREAD_HTTP, string $SECRET_KEY = "secret")
    {
        self::$LOCATION_THREAD_HTTP = $LOCATION_THREAD_HTTP;
        self::$SECRET_KEY = $SECRET_KEY;
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
        $callMorph = new CallMorph(self::$SECRET_KEY);

        foreach ($script as $key => $value) {
            $script[$key] = self::textProtect($callMorph->serialize($value));
        }

        if (!$waitResponse && !$returnPromise) {
            foreach ($script as $key => $value) {
                $ch = curl_init();
                curl_setopt_array($ch, [
                    CURLOPT_URL => $threadHttp,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POSTFIELDS => ['script' => $value],
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_FRESH_CONNECT => true,
                    CURLOPT_CONNECTTIMEOUT => 0,
                    CURLOPT_TIMEOUT_MS => 2000 // Wait 2s
                ]);

                $response = json_decode(self::textUnprotect(curl_exec($ch)), true);
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
                CURLOPT_POSTFIELDS => ['script' => $value],
            ]);
            curl_multi_add_handle($mch, $script[$key]);
        }

        $resultCurl = function () use (&$mch, &$script, $infoRequest) {
            foreach ($script as $key => $ch) {
                $response = json_decode(self::textUnprotect(curl_multi_getcontent($ch)), true);
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

    private static function textProtect(string $text)
    {
        $aes = new SimpleAES256(self::$SECRET_KEY);
        $text = $aes->encrypt_cbc($text);
        $text = base64_encode($text);
        return $text;
    }

    private static function textUnprotect(string $text)
    {
        $aes = new SimpleAES256(self::$SECRET_KEY);
        $text = base64_decode($text);
        $text = $aes->decrypt_cbc($text);
        return $text;
    }

    private static function rpcThreadParallel(string $script): string
    {
        $script = self::textUnprotect($script);
        if (!empty($script)) {
            ob_start();
            $returned = null;
            try {
                $callMorph = new CallMorph(self::$SECRET_KEY);
                $returned = $callMorph->unserialize($script)();
            } catch (\Throwable $th) {
                var_dump($th);
            }
            $printed = ob_get_clean();
            if (empty($printed) && empty($returned)) {
                $script = "";
            } else if (!empty($printed) && empty($returned)) {
                $script = $printed;
            } else if (empty($printed) && !empty($returned)) {
                $script = $returned;
            } else {
                $script = [
                    "printed" => &$printed,
                    "returned" => &$returned
                ];
            }
        } else {
            $script = "";
        }
        return self::textProtect(json_encode($script));
    }

    public static function rpcProcess(string $script): string
    {
        return self::rpcThreadParallel($script);
    }
}
