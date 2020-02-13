<?php

namespace Crm\ApplicationModule\Helpers;

use Crm\ApplicationModule\Config\ApplicationConfig;
use Nette\Utils\Html;

class PriceHelper
{
    private $applicationConfig;

    public function __construct(ApplicationConfig $applicationConfig)
    {
        $this->applicationConfig = $applicationConfig;
    }

    public function getFormattedPrice($value, $currency = null): string
    {
        if (!$currency) {
            $currency = $this->applicationConfig->get('currency');
        }

        // TODO - refactor with https://akrabat.com/using-phps-numberformatter-to-format-currencies/

        if ($currency == 'EUR') {
            $text = number_format($value, 2, ',', ' ') . '&nbsp;&euro;';
        } elseif ($currency == 'CZK') {
            $text = number_format($value, 2, ',', ' ') . '&nbsp;Kč';
        } elseif ($currency == 'USD') {
            $text = '$ ' . number_format($value, 2, '.', ',');
        } else {
            $text = $value;
        }

        return $text;
    }

    public function process($value, $currency = null)
    {
        return Html::el('span')->setHtml($this->getFormattedPrice($value, $currency));
    }
}
