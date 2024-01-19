<?php

namespace Crm\ApplicationModule\Models\Snippet;

use Contributte\Translation\Translator;
use Crm\ApplicationModule\Repositories\SnippetsRepository;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class SnippetRenderer
{
    public function __construct(
        private SnippetsRepository $snippetsRepository,
        private Translator $translator,
    ) {
    }

    public function render($key)
    {
        $params = [
            'locale' => $this->translator->getLocale(),
        ];
        if (is_array($key)) {
            $params = array_diff_key($key, [0 => true]);
            $key = $key[0];
        }

        $snippets = $this->snippetsRepository->loadAllByIdentifier($key);

        foreach ($snippets as $snippet) {
            $this->snippetsRepository->markUsed($snippet);

            $loader = new ArrayLoader([
                'snippet' => $snippet->html,
            ]);
            $twig = new Environment($loader);
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
