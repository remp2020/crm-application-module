<?php

namespace Crm\ApplicationModule\Models\Snippet\Control;

use Crm\ApplicationModule\Snippet\SnippetRenderer;
use Nette\Application\UI\Control;

class Snippet extends Control
{
    private $snippetRenderer;

    public function __construct(SnippetRenderer $snippetRenderer)
    {
        $this->snippetRenderer = $snippetRenderer;
    }

    public function render($key)
    {
        $template = $this->snippetRenderer->render($key);
        if ($template === false) {
            echo "<!-- not snippet for key '{$this->snippetRenderer->getKey($key)}' -->";
        }
        echo $template;
    }
}
