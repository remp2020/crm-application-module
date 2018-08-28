<?php

namespace Crm\ApplicationModule\Helpers;

class DiffHelper
{
    public function process($arg1, $arg2)
    {
        $diff = $arg1 - $arg2;

        $class = '';
        if ($diff < 0) {
            $class = 'progress-bar-danger';
        } elseif ($diff > 0) {
            $class = 'progress-bar-success';
        }

        $content = '<div class="pull-right">';
        $content .= '<span class="badge ' . $class . '" style="font-size:0.9em">';
        $content .= '<b>' . intval($arg1) . '</b>&nbsp;';
        $content .= '<i>';

        if ($diff > 0) {
            $content .= '+';
        }
        $content .= round($diff, 2);
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
        $content .= '</div>';
        return $content;
    }
}
