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

    public function process($value, $currency = null)
    {
        if (!$currency) {
            $currency = $this->applicationConfig->get('currency');
        }

        // TODO - refactor with https://akrabat.com/using-phps-numberformatter-to-format-currencies/

        if ($currency == 'EUR') {
            $html = number_format($value, 2, ',', ' ') . '&nbsp;&euro;';
        } else if ($currency == 'CZK') {
            $html = number_format($value, 2, ',', ' ') . '&nbsp;Kč';
        } else if ($currency == 'USD') {
            $html = '$ ' . number_format($value, 2, '.', ',');
        } else {
            $html = $value;
        }

        return Html::el('span')->setHtml($html);
    }
}
