<?php

namespace Crm\ApplicationModule\Helpers;

class DiffPriceHelper
{
    private $priceHelper;

    public function __construct(PriceHelper $priceHelper)
    {
        $this->priceHelper = $priceHelper;
    }

    public function process($arg1, $arg2, $precision = 2)
    {
        $diff = $arg1 - $arg2;

        $class = '';
        if ($diff < 0) {
            $class = 'progress-bar-danger';
        } elseif ($diff > 0) {
            $class = 'progress-bar-success';
        }

        $content = '<span class="badge" style="font-size:0.9em">';
        $content .= '<b>' . $this->priceHelper->getFormattedPrice($arg1, null, $precision) . '</b>';
        $content .= '</span>&nbsp;';
        $content .= '<span class="badge ' . $class . '" style="font-size:0.8em">';
        $content .= '<i>';

        if ($diff >= 0) {
            $content .= '+';
        }
        $content .= $this->priceHelper->getFormattedPrice($diff, null, $precision);
        if ($arg1 == 0 && $arg2 == 0) {
            $per = 0;
        } else {
            if ($arg1 == 0 || $arg2 == 0) {
                $per = 100;
            } else {
                $per = $arg1 / $arg2 * 100 - 100;
            }
        }

        $content .= ' (' . round($per, 1) . '%)';

        $content .= '</i>';
        $content .= '</span>';
        return $content;
    }
}
