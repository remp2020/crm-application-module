<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\LatteFunctions;

function escapehtml(string $input): string
{
    return htmlspecialchars($input, ENT_QUOTES);
}

class EscapeHTMLFunction
{
    public function process(string $input): string
    {
        return escapehtml($input);
    }
}
