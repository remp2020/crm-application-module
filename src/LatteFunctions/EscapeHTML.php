<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\LatteFunctions;

class EscapeHTML
{
    public static function escape(string $input): string
    {
        return htmlspecialchars($input, ENT_QUOTES);
    }
}
