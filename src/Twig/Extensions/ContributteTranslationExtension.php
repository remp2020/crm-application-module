<?php declare(strict_types=1);

namespace Crm\ApplicationModule\Twig\Extensions;

use Contributte\Translation\Translator;
use Exception;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class ContributteTranslationExtension extends AbstractExtension
{
    public function __construct(
        private readonly Translator $translator,
    ) {
    }

    public function getFilters()
    {
        return [
            new TwigFilter('trans', $this->trans(...)),
        ];
    }

    /**
     * The function signature is meant to be compatible with the Symfony built-in trans filter (as a forwards compatibility).
     * Just unnecessary type hints was omitted for now.
     */
    public function trans(?string $message, array|string $arguments = [], ?string $domain = null, ?string $locale = null): string
    {
        if (!is_array($arguments)) {
            throw new Exception(sprintf(
                'Arguments should be an array of parameters, %s given.',
                get_debug_type($arguments),
            ));
        }

        return $this->translator->translate($message, $arguments, $domain, $locale);
    }
}
