<?php

if (!function_exists('_e')) {

    /**
     *
     * Return translate text
     *
     * @param $text
     * @return string
     */
    function _e($text)
    {
        $text = (!empty($GLOBALS['traducciones'][$text]) && !empty($GLOBALS['traducciones'][$text]['msgstr'][0])) ? $GLOBALS['traducciones'][$text]['msgstr'][0] : $text;
        return stripslashes($text);
    }
}

if (!function_exists('dd')) {

    /**
     * return var_dump parameters passed and die execution page.
     */
    function dd()
    {
        ini_set('xdebug.var_display_max_depth', -1);
        ini_set('xdebug.var_display_max_children', -1);
        ini_set('xdebug.var_display_max_data', -1);
        array_map(function ($x) {
            var_dump($x);
        }, func_get_args());
        die;
    }
}

if (!function_exists('slugify')) {

    function slugify($text)
    {
        // replace non letter or digits by -
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate -
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }

        return $text;
    }
}

if (!function_exists('randomString')) {
    function randomString($length = 10, $uc = true, $n = true, $sc = false)
    {
        $rstr = "";
        $source = 'abcdefghijklmnopqrstuvwxyz';
        if ($uc == 1) {
            $source .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        }
        if ($n == 1) {
            $source .= '1234567890';
        }
        if ($sc == 1) {
            $source .= '|@#~$%()=^*+[]{}-_';
        }
        if ($length > 0) {
            $source = str_split($source, 1);
            for ($i = 1; $i <= $length; $i++) {
                mt_srand((double)microtime() * 1000000);
                $num = mt_rand(1, count($source));
                $rstr .= $source[$num - 1];
            }

        }

        return $rstr;
    }
}

if (!function_exists('boot_detected')) {
    function boot_detected()
    {
        if (isset($_SERVER['HTTP_USER_AGENT']) && preg_match('/bot|crawl|slurp|spider/i',
                $_SERVER['HTTP_USER_AGENT'])
        ) {
            return true;
        }
        return false;
    }
}

if (!function_exists('preview_request')) {
    function preview_request()
    {
        if ((isset($_SERVER["HTTP_X_PURPOSE"]) && (strtolower($_SERVER["HTTP_X_PURPOSE"]) == "preview")) ||
            (isset($_SERVER["HTTP_X_MOZ"]) && (strtolower($_SERVER["HTTP_X_MOZ"]) == "prefetch"))
        ) {
            return true;
        }

        return false;
    }
}

if (!function_exists('getRealIpAdress')) {
    function getRealIpAdress()
    {
        if (isset($_SERVER["HTTP_CLIENT_IP"])) {
            return $_SERVER["HTTP_CLIENT_IP"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_X_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_X_FORWARDED"])) {
            return $_SERVER["HTTP_X_FORWARDED"];
        } elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])) {
            return $_SERVER["HTTP_FORWARDED_FOR"];
        } elseif (isset($_SERVER["HTTP_FORWARDED"])) {
            return $_SERVER["HTTP_FORWARDED"];
        } else {
            return $_SERVER["REMOTE_ADDR"];
        }
    }
}

if (!function_exists('html_entity_decode_with_quotes')) {
    function html_entity_decode_with_quotes($text){
        return html_entity_decode($text, ENT_QUOTES);
    }
}
