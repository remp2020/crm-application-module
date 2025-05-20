<?php

namespace Crm\ApplicationModule\Helpers;

use DateTimeInterface;
use IntlDateFormatter;
use Nette\Localization\Translator;

class UserDateHelper
{
    private $translator;

    private $format;

    private $shortFormat = 'dd.MM.yyyy HH:mm:ss';

    private $longFormat = 'd. MMMM yyyy HH:mm:ss';

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * setFormat accepts any format supported by IntlDateFormatter.
     *
     * @param array|string $format
     */
    public function setFormat($format)
    {
        $this->format = $format;
    }

    /**
     * setShortFormat accepts any format supported by IntlDateFormatter.
     *
     * @param array|string $format
     */
    public function setShortFormat($format)
    {
        $this->shortFormat = $format;
    }

    /**
     * setLongFormat accepts any format supported by IntlDateFormatter.
     *
     * @param array|string $format
     */
    public function setLongFormat($format)
    {
        $this->longFormat = $format;
    }

    public function process($date, $long = false)
    {
        if (!$date instanceof DateTimeInterface) {
            return (string) $date;
        }

        if ($this->format) {
            $format = $this->format;
        } elseif ($long) {
            $format = $this->longFormat;
        } else {
            $format = $this->shortFormat;
        }

        return IntlDateFormatter::formatObject(
            $date,
            $format,
            $this->translator->getLocale(),
        );
    }
}
