<?php

namespace Crm\ApplicationModule\Helpers;

use Kdyby\Translation\Translator;

class YesNoHelper
{
    /** @var Translator */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function process($bool)
    {
        $key = $bool ? 'yes' : 'no';
        return $this->translator->translate("system.{$key}");
    }
}
