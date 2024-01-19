<?php

namespace Crm\ApplicationModule\Application;

use Nette\Utils\Strings;
use Tracy\Debugger;
use Tracy\ILogger;

class EnvironmentConfig
{
    public function get($key)
    {
        $val = Core::env($key);
        if ($val === null || $val === '') {
            return null;
        }
        return $val;
    }

    public function getDsn()
    {
        $port = Core::env('CRM_DB_PORT');
        if (!$port) {
            $port = 3306;
        }
        return Core::env('CRM_DB_ADAPTER') .
            ':host=' . Core::env('CRM_DB_HOST') .
            ';dbname=' . Core::env('CRM_DB_NAME') .
            ';port=' . $port;
    }

    public static function getCrmKey(): string
    {
        $key = Core::env('CRM_KEY', '');
        if (!$key) {
            Debugger::log("Empty CRM_KEY, please run 'application:generate_key' command to properly initialize application key.", ILogger::WARNING);
        }
        if (Strings::startsWith($key, 'base64:')) {
            $key = base64_decode(substr($key, 7));
        }
        return $key;
    }
}
