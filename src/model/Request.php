<?php

namespace Crm\ApplicationModule;

// hnusna skareda staticka vec! fuj!
class Request
{
    public static function getIp(): string
    {
        $serverKeysToCheck = [
            'HTTP_R_FORWARDED_FOR', // special header for custom IP if needed
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
            'HTTP_CLIENT_IP',
        ];

        foreach ($serverKeysToCheck as $serverKey) {
            if (!self::validateAddress($serverKey)) {
                continue;
            }
            return self::extractFirstAddress($serverKey);
        }

        return 'cli';
    }

    private static function validateAddress(string $serverKey): bool
    {
        $address = self::extractFirstAddress($serverKey);
        if ($address == '127.0.0.1') {
            return false;
        }
        return (bool) filter_var($address, FILTER_VALIDATE_IP);
    }

    private static function extractFirstAddress(string $serverKey): ?string
    {
        $filterValue = filter_input(INPUT_SERVER, $serverKey);
        if (strpos($filterValue, ',') === false) {
            return $filterValue;
        }

        $ips = array_map('trim', explode(',', $filterValue));
        return current($ips);
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
