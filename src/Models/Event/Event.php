<?php

namespace Crm\ApplicationModule\Models\Event;

class Event
{
    public $type;

    public $value;

    public $score;

    public function __construct($type, $value, $score)
    {
        $this->type = $type;
        $this->value = $value;
        $this->score = $score;
    }
}
