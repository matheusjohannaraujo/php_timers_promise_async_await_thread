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

    protected static function prepareScripts(callable|array $scripts)
    {
        if (is_callable($scripts)) {
            $scripts = [$scripts];
        }

        if (!is_array($scripts)) {
            $scripts = [fn() => print("invalid script")];
        }

        $callMorph = new CallMorph(self::$SECRET_KEY);

        foreach ($scripts as $key => $script) {
            $scripts[$key] = self::textProtect($callMorph->serialize($script));
        }

        return $scripts;
    }

    public static function rpcSend(callable|array $scripts, int $waitResponseSeconds = 0, ?string $threadHttp = null, bool $infoRequest = false)
    {
        $scripts = self::prepareScripts($scripts);
        $threadHttp ??= self::$LOCATION_THREAD_HTTP;

        $mch = curl_multi_init();
        $curlHandles = [];

        foreach ($scripts as $payload) {
            $ch = curl_init();
            $curlOptions = [
                CURLOPT_URL => $threadHttp,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POSTFIELDS => ['script' => $payload],
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_CONNECTTIMEOUT => 0,
            ];

            if ($waitResponseSeconds > 0) {
                $curlOptions[CURLOPT_TIMEOUT_MS] = $waitResponseSeconds * 1000;
            }

            curl_setopt_array($ch, $curlOptions);
            curl_multi_add_handle($mch, $ch);
            $curlHandles[] = $ch;
        }

        return new Promise(function ($resolve, $reject) use (&$mch, &$curlHandles, $infoRequest) {
            $uid = Timers::setInterval(function () use (&$uid, &$resolve, &$reject, &$mch, &$curlHandles, $infoRequest) {
                $active = null;
                curl_multi_exec($mch, $active);

                if ($active > 0) {
                    return;
                }

                Timers::clearInterval($uid);

                $results = [];
                $hasError = false;

                foreach ($curlHandles as $ch) {
                    $raw = curl_multi_getcontent($ch);
                    $response = null;

                    if ($raw !== false) {
                        $decrypted = self::textUnprotect($raw);
                        $response = json_decode($decrypted, true);
                    }

                    $error = curl_errno($ch) ? curl_error($ch) : null;

                    if ($error) {
                        $hasError = true;
                    }

                    $results[] = [
                        'response' => $response,
                        'error'    => $error,
                        'info'     => $infoRequest ? curl_getinfo($ch) : null,
                    ];

                    curl_multi_remove_handle($mch, $ch);
                    curl_close($ch);
                }

                curl_multi_close($mch);

                if ($hasError) {
                    $reject(count($results) === 1 ? $results[0] : $results);
                } else {
                    $resolve(count($results) === 1 ? $results[0] : $results);
                }
            }, 50);
        });
    }

    public static function rpcProcess(string $script): string
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
}
