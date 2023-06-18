<?php

namespace Crm\ApplicationModule;

// hnusna skareda staticka vec! fuj!
class Request
{
    public static function getIp()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARDED_FOR'] != '127.0.0.1') {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            $ip = 'cli';
        }
        return $ip;
    }

    public static function getUserAgent()
    {
        return $_SERVER['HTTP_USER_AGENT'] ?? '';
    }

    public static function getDomain()
    {
        $host = $_SERVER['HTTP_HOST'] ?? '';

        // remove port from host
        if (($index = strrpos($host, ':')) !== false) {
            $host = substr($host, 0, $index);
        }

        // remove subdomain from host
        if (($index = strrpos($host, '.')) !== false) {
            if (($index = strrpos($host, '.', $index - strlen($host) - 1)) !== false) {
                $host = substr($host, $index);
            }
        }

        return $host;
    }

    public static function isApi()
    {
        if (!isset($_SERVER['REQUEST_URI'])) {
            return false;
        }
        return preg_match('#^/api/v\d+/#', $_SERVER['REQUEST_URI']);
    }
}
