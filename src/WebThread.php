<?php

/*
	GitHub: https://github.com/matheusjohannaraujo/zynq
	Country: Brasil
	State: Pernambuco
	Developer: Matheus Johann Araujo
	Date: 2025-05-01
*/

namespace MJohann\Packlib;

use MJohann\Packlib\CallMorph;
use MJohann\Packlib\SimpleAES256;

class WebThread
{

    private static string $LOCATION_WEB_THREAD_HTTP = "http://localhost/rpc.php";
    private static string $SECRET_KEY = "secret";

    public static function init(string $LOCATION_WEB_THREAD_HTTP, string $SECRET_KEY = "secret")
    {
        self::$LOCATION_WEB_THREAD_HTTP = $LOCATION_WEB_THREAD_HTTP;
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

    private static function prepareScripts(callable|array $scripts)
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

    public static function rpcSend(callable|array $scripts, int $waitResponseSeconds = 0, ?string $threadHttp = null, bool $infoRequest = true): Promise|array
    {
        $isSingleCallable = is_callable($scripts);
        $scripts = self::prepareScripts($scripts);
        $threadHttp ??= self::$LOCATION_WEB_THREAD_HTTP;

        $handles = [];
        $promises = [];
        $mch = curl_multi_init();

        // Define 600 segundos como tempo máximo de espera
        if ($waitResponseSeconds === 0) {
            $waitResponseSeconds = 600;
        }

        // Para cada script (ou chamada de função no array de callables)
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
            $handles[] = $ch;
            $promises[] = new Promise();
        }

        $uid = null;
        $uid = Timers::setInterval(function () use (&$uid, &$mch, &$handles, &$promises, $infoRequest) {
            $active = null;
            curl_multi_exec($mch, $active);
            $info = curl_multi_info_read($mch);

            // Verifica quais terminaram
            if (is_array($info)) {
                $ch = $info['handle'];
                $idPromisse = -1;

                foreach ($handles as $key => $handle) {
                    if ($handle === $ch) {
                        $idPromisse = $key;
                        break;
                    }
                }

                $response = curl_multi_getcontent($ch);
                $error = curl_errno($ch) ? curl_error($ch) : null;
                $info = $infoRequest ? curl_getinfo($ch) : null;

                if ($response !== false) {
                    // Descriptografar e processar a resposta
                    $decrypted = self::textUnprotect($response);
                    $response = json_decode($decrypted, true);
                }

                // Resolver ou rejeitar a Promise dependendo do erro
                if ($error !== null) {
                    // Rejeita a Promise se erro
                    $promises[$idPromisse]->reject($error, $info);
                } else {
                    // Resolve a Promise se não houver erro
                    $promises[$idPromisse]->resolve($response, $info);
                }

                curl_multi_remove_handle($mch, $ch);
                curl_close($ch);
                unset($handles[$idPromisse]);
                unset($promises[$idPromisse]);
            }
            if ($active === 0 || $active === null) {
                foreach ($promises as $key => $promise) {
                    if ($promises[$key] !== null) {
                        $promises[$key]->reject(null);
                        unset($promises[$key]);
                    }
                }
                unset($handles);
                unset($promises);
                curl_multi_close($mch);
                Timers::clearInterval($uid);
            }
        }, 50);

        Timers::workRun();

        // Se for um único callable, retorna uma única Promise, caso contrário, retorna um array de Promises
        return $isSingleCallable ? $promises[0] : $promises;
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
