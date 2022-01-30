<?php

namespace Crm\ApplicationModule\Helpers;

use Kdyby\Translation\Translator;
use Nette\Utils\Html;

class ActiveLabelHelper
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function process($active)
    {
        if ($active) {
            return Html::el('span', ['class' => 'label label-success'])
                ->setText($this->translator->translate('system.activated'));
        } else {
            return Html::el('span', ['class' => 'label label-default'])
                ->setText($this->translator->translate('system.deactivated'));
        }
    }
}
