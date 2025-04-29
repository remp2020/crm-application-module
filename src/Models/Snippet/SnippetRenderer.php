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

    public function render(string|array $key): ?string
    {
        $params = [];
        if (is_array($key)) {
            $params = array_diff_key($key, [0 => true]);
            $key = $key[0];
        }

        // If locale was not set for snippet, use locale from translator
        $params['locale'] ??= $this->translator->getLocale();

        $snippet = $this->snippetsRepository->loadByIdentifier($key);
        if (!$snippet || !$snippet->is_active) {
            return null;
        }

        $this->snippetsRepository->markUsed($snippet);

        $loader = new ArrayLoader([
            'snippet' => $snippet->html,
        ]);

        return (new Environment($loader))->render('snippet', $params);
    }

    public function getKey($key)
    {
        if (is_array($key)) {
            $key = $key[0];
        }

        return $key;
    }
}
