<?php

namespace Crm\ApplicationModule\Events;

use Nette\Database\Table\ActiveRow;

interface ConfigEventInterface
{
    public function getConfig(): ActiveRow;
}
