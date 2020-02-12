<?php

namespace Crm\ApplicationModule\Snippet;

use Crm\ApplicationModule\Snippet\Repository\SnippetsRepository;

class SnippetRenderer
{
    private $snippetsRepository;

    public function __construct(SnippetsRepository $snippetsRepository)
    {
        $this->snippetsRepository = $snippetsRepository;
    }

    public function render($key)
    {
        $params = [];
        if (is_array($key)) {
            $params = array_diff_key($key, [0 => true]);
            $key = $key[0];
        }

        $snippets = $this->snippetsRepository->loadAllByIdentifier($key);

        foreach ($snippets as $snippet) {
            $this->snippetsRepository->markUsed($snippet);

            $loader = new \Twig\Loader\ArrayLoader([
                'snippet' => $snippet->html,
            ]);
            $twig = new \Twig\Environment($loader);
            $template = $twig->render('snippet', $params);
            return $template;
        }
        return false;
    }

    public function getKey($key)
    {
        if (is_array($key)) {
            $key = $key[0];
        }
        return $key;
    }
}
