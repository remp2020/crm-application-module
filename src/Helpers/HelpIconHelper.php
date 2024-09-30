<?php

namespace Crm\ApplicationModule\Helpers;

use Latte\ContentType;
use Latte\Runtime\FilterInfo;

class HelpIconHelper
{
    public function process(FilterInfo $filterInfo, $message)
    {
        $filterInfo->contentType = ContentType::Html;

        return '<i class="fa fa-question-circle" data-toggle="tooltip" data-original-title="'.$message.'"></i>';
    }
}
