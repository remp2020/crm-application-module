<?php

namespace Crm\ApplicationModule\Helpers;

use Nette\Utils\Json;

class JsonHelper
{
    public function process($input): string
    {
        if (is_array($input) || is_object($input)) {
            return Json::encode($input, Json::PRETTY);
        }

        return Json::encode(Json::decode($input), Json::PRETTY);
    }
}
