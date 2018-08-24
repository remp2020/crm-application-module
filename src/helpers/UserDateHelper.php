<?php

namespace Crm\ApplicationModule\Helpers;

use DateTime;
use IntlDateFormatter;
use Kdyby\Translation\Translator;

class UserDateHelper
{
    /** @var Translator  */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function process($date, $long = false)
    {
        if (!$date instanceof DateTime) {
            return (string) $date;
        };

        if ($long) {
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
