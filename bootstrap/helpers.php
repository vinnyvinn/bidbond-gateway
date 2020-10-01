<?php

use Illuminate\Support\Str;

if (!function_exists('generateEmailCode')) {
    function generateEmailCode()
    {
        do {
            $code = Str::random(6);
        } while (\App\Code::where('code_email', $code)->count() > 0);
        return $code;
    }
}

if (!function_exists('unencodePhone')) {
    function unencodePhone($phone)
    {
        $prefix = "254";
        if ($prefix == substr($phone, 0, 3)) {
            $phone = "0" . substr($phone, 3, strlen($phone) - 3);
        }
        return $phone;
    }
}

if (!function_exists('generatePhoneCode')) {
    function generatePhoneCode()
    {
        do {
            $code = Str::random(6);
        } while (\App\Code::where('code_phone', $code)->count() > 0);
        return $code;
    }
}

if (!function_exists('crypto_rand_secure')) {
    function crypto_rand_secure($min, $max)
    {
        $range = $max - $min;
        if ($range < 1) return $min; // not so random...
        $log = ceil(log($range, 2));
        $bytes = (int)($log / 8) + 1; // length in bytes
        $bits = (int)$log + 1; // length in bits
        $filter = (int)(1 << $bits) - 1; // set all lower bits to 1
        do {
            $rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
            $rnd = $rnd & $filter; // discard irrelevant bits
        } while ($rnd > $range);
        return $min + $rnd;
    }
}

if (!function_exists('safeFileName')) {
    function safeFileName($file)
    {
        $file = mb_ereg_replace("([^\w\s\d\-_~,;\[\]\(\).])", '', $file);
        $file = mb_ereg_replace("([\.]{2,})", '', $file);
        return $file;
    }
}

if (!function_exists('current_path')) {
    function current_path()
    {
        $url = parse_url(url()->current());
        return $url['path'];
    }
}

if (!function_exists('getToken')) {
    function getToken($length = 6, $type = 'capnum', $prefix = '')
    {
        switch ($type) {
            case 'alnum':
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'capnum':
                $pool = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'alpha':
                $pool = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            case 'hexdec':
                $pool = '0123456789abcdef';
                break;
            case 'numeric':
                $pool = '0123456789';
                break;
            case 'nozero':
                $pool = '123456789';
                break;
            case 'distinct':
                $pool = '2345679ACDEFHJKLMNPRSTUVWXYZ';
                break;
            default:
                $pool = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }

        $token = "";

        $max = strlen($pool);

        for ($i = 0; $i < $length; $i++) {
            $token .= $pool[crypto_rand_secure(0, $max - 1)];
        }

        return $prefix . $token;
    }

    if ( ! function_exists('put_permanent_env'))
    {
        function put_permanent_env($key, $value)
        {
            $path = app()->environmentFilePath();

            $escaped = preg_quote('='.env($key), '/');

            file_put_contents($path, preg_replace(
                "/^{$key}{$escaped}/m",
                "{$key}={$value}",
                file_get_contents($path)
            ));
        }
    }
}