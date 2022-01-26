<?php

namespace Crm\ApplicationModule\Hermes;

use Tomaj\Hermes\Message;

class HermesMessage extends Message
{
    public const PRIORITY_LOW = 50;
    public const PRIORITY_DEFAULT = 100;
    public const PRIORITY_HIGH = 200;
}
