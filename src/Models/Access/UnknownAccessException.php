<?php

namespace Crm\ApplicationModule\Models\Access;

use Throwable;

class UnknownAccessException extends \Exception
{
    private $access;

    public function __construct(string $access, string $message = "", int $code = 0, Throwable $previous = null)
    {
        $this->access = $access;
        parent::__construct($message, $code, $previous);
    }
}
