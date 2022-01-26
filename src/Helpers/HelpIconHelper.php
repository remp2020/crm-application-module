<?php

namespace Crm\ApplicationModule\Helpers;

class HelpIconHelper
{
    public function process($message)
    {
        return '<i class="fa fa-question-circle" data-toggle="tooltip" data-original-title="'.$message.'"></i>';
    }
}
