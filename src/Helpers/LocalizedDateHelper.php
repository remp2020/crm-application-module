<?php

namespace Crm\ApplicationModule\Helpers;

use Contributte\Translation\Translator;
use IntlDateFormatter;
use Nette\Utils\DateTime;

class LocalizedDateHelper
{
    private array $longFormat = [
        null => [IntlDateFormatter::LONG, IntlDateFormatter::MEDIUM],
    ];

    private array $shortFormat = [
        null => [IntlDateFormatter::SHORT, IntlDateFormatter::MEDIUM],
    ];

    public function __construct(private Translator $translator)
    {
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

    public function process($date, bool $long = false, bool $includeTime = true)
    {
        if (!$date instanceof \DateTimeInterface) {
            $date = DateTime::from($date);
        }

        $locale = $this->translator->getLocale();

        $format = $this->getFormat($long, $locale);
        if (!$includeTime) {
            $format[1] = IntlDateFormatter::NONE;
        }

        return IntlDateFormatter::formatObject(
            $date,
            $format,
            $locale,
        );
    }
}
