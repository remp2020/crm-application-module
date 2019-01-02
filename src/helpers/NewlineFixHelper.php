<?php

namespace Crm\ApplicationModule\Helpers;

/**
 * Transforms newline to cross-platform compatible newline. Also fixes newlines within single quoted strings.
 */
class NewlineFixHelper
{
    public function process($value)
    {
        return str_replace('\n', PHP_EOL, $value);
    }
}
