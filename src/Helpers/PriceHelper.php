<?php

namespace Crm\ApplicationModule\Helpers;

use Crm\ApplicationModule\Models\Config\ApplicationConfig;
use Nette\Utils\Html;

class PriceHelper
{
    public function __construct(
        private ApplicationConfig $applicationConfig,
    ) {
    }

    public function getFormattedPrice(
        $value,
        ?string $currency = null,
        int $precision = 2,
        bool $withoutCurrencySymbol = false,
    ): ?string {
        if (!$currency) {
            $currency = (string) $this->applicationConfig->get('currency');
        }

        // TODO - refactor with https://akrabat.com/using-phps-numberformatter-to-format-currencies/

        if ($currency === 'EUR') {
            $text = number_format($value, $precision, ',', ' ');
            if (!$withoutCurrencySymbol) {
                $text .= '&nbsp;&euro;';
            }
        } elseif ($currency === 'CZK') {
            $text = number_format($value, $precision, ',', ' ');
            if (!$withoutCurrencySymbol) {
                $text .= '&nbsp;Kč';
            }
        } elseif ($currency === 'USD') {
            $text = number_format($value, $precision, '.', ',');
            if (!$withoutCurrencySymbol) {
                $text = '$&nbsp;' . $text;
            }
        } else {
            $text = $value;
        }

        return $text;
    }

    public function process($value, ?string $currency = null, int $precision = 2, bool $withoutCurrencySign = false)
    {
        return Html::el('span')->setHtml($this->getFormattedPrice($value, $currency, $precision, $withoutCurrencySign));
    }
}
