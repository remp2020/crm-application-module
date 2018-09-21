<?php

namespace Crm\ApplicationModule\Helpers;

use DateTime;
use IntlDateFormatter;
use Nette\Localization\ITranslator;

class UserDateHelper
{
    private $translator;

    private $format;

    public function __construct(ITranslator $translator)
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

    public function process($date, $long = false)
    {
        if (!$date instanceof DateTime) {
            return (string) $date;
        };

        if ($this->format) {
            $format = $this->format;
        } elseif ($long) {
            $format = "d. MMMM yyyy HH:mm:ss";
        } else {
            $format = "dd.MM.yyyy HH:mm:ss";
        }

        return IntlDateFormatter::formatObject(
            $date,
            $format,
            $this->translator->getLocale()
        );
    }
}
