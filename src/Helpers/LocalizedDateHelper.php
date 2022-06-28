<?php

namespace Crm\ApplicationModule\Helpers;

use DateTime;
use IntlDateFormatter;
use Nette\Localization\Translator;

class LocalizedDateHelper
{
    private Translator $translator;

    private array $longFormat = [
        null => [IntlDateFormatter::LONG, IntlDateFormatter::MEDIUM]
    ];

    private array $shortFormat = [
        null => [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM]
    ];

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * setShortFormat accepts any format supported by IntlDateFormatter.
     *
     * @param array|string $format
     */
    public function setShortFormat($format, string $locale = null)
    {
        $this->shortFormat[$locale] = $format;
    }

    /**
     * setLongFormat accepts any format supported by IntlDateFormatter.
     *
     * @param array|string $format
     */
    public function setLongFormat($format, string $locale = null)
    {
        $this->longFormat[$locale] = $format;
    }

    public function getFormat(bool $long = false, string $locale = null)
    {
        if ($long) {
            return $this->longFormat[$locale] ?? $this->longFormat[null];
        }

        return $this->shortFormat[$locale] ?? $this->shortFormat[null];
    }

    public function process($date, $long = false)
    {
        if (!$date instanceof DateTime) {
            return (string)$date;
        }

        $locale = $this->translator->getLocale();
        $format = $this->getFormat($long, $locale);

        return IntlDateFormatter::formatObject(
            $date,
            $format,
            $locale
        );
    }
}
