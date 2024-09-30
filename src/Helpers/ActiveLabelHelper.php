<?php

namespace Crm\ApplicationModule\Helpers;

use Contributte\Translation\Translator;
use Latte\ContentType;
use Latte\Runtime\FilterInfo;
use Nette\Utils\Html;

class ActiveLabelHelper
{
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function process(FilterInfo $filterInfo, $active)
    {
        $filterInfo->contentType = ContentType::Html;

        if ($active) {
            return Html::el('span', ['class' => 'label label-success'])
                ->setText($this->translator->translate('system.activated'));
        } else {
            return Html::el('span', ['class' => 'label label-default'])
                ->setText($this->translator->translate('system.deactivated'));
        }
    }
}
