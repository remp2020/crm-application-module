<?php

namespace Crm\ApplicationModule\Models\Snippet\Control;

use Crm\ApplicationModule\Models\Snippet\SnippetRenderer;
use Nette\Application\UI\Control;

class Snippet extends Control
{
    private SnippetRenderer $snippetRenderer;

    public function __construct(SnippetRenderer $snippetRenderer)
    {
        $this->snippetRenderer = $snippetRenderer;
    }

    public function render(string|array $key): void
    {
        $template = $this->snippetRenderer->render($key);
        if ($template === null) {
            echo "<!-- not snippet for key '{$this->snippetRenderer->getKey($key)}' -->";
        }

        echo $template;
    }
}
