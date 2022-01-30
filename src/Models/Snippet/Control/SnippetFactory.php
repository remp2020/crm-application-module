<?php

namespace Crm\ApplicationModule\Snippet\Control;

interface SnippetFactory
{
    /** @return Snippet */
    public function create();
}
