<?php

namespace Crm\ApplicationModule\Models\Snippet\Control;

interface SnippetFactory
{
    public function create(): Snippet;
}
