<?php

namespace Crm\ApplicationModule;

class EnvironmentConfig
{
    public function get($key)
    {
        $val = getenv($key);
        if ($val) {
            return $val;
        }
        return null;
    }

    public function getDsn()
    {
        $port = $this->get('CRM_DB_PORT');
        if (!$port) {
            $port = 3306;
        }
        return $this->get('CRM_DB_ADAPTER') .
            ':host=' . $this->get('CRM_DB_HOST') .
            ';dbname=' . $this->get('CRM_DB_NAME') .
            ';port=' . $port;
    }
}
