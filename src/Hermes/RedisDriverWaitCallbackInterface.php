<?php
declare(strict_types=1);

namespace Crm\ApplicationModule\Hermes;

interface RedisDriverWaitCallbackInterface
{
    public function call(): void;
}
