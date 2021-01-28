<?php

namespace Crm\ApplicationModule\Presenters;

class SnippetsPresenter extends FrontendPresenter
{
    public $autoCanonicalize = false;

    public function renderDefault($key = 'default')
    {
        $this->template->key = $key;
    }
}
