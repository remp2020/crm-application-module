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
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    }

    public static function getDomain()
    {
        if (!isset($_SERVER['HTTP_HOST'])) {
            return '';
        }
        $parts = explode('.', $_SERVER['HTTP_HOST']);
        if (count($parts) > 2) {
            return '.' . $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }

        // remove port from HTTP_HOST if present
        $parts = explode(':', $_SERVER['HTTP_HOST']);
        if (count($parts) > 1) {
            return $parts[0];
        }

        return $_SERVER['HTTP_HOST'];
    }

    public static function isApi()
    {
        return substr($_SERVER['REQUEST_URI'], 0, 5) != '/api/';
    }
}
