<?php

namespace Crm\ApplicationModule\Components;

use Nette\Application\UI\Control;
use Nette\Utils\Paginator;

/**
 * Visual paginator control.
 *
 * Simple component used for rendering listing pagination.
 *
 * @author     David Grudl
 * @copyright  Copyright (c) 2009 David Grudl
 * @package    Nette Extras
 */
class VisualPaginator extends Control
{
    /** @var Paginator */
    private $paginator;

    /** @persistent */
    public $page = 1;

    /**
     * @return Paginator
     */
    public function getPaginator()
    {
        if (!$this->paginator) {
            $this->paginator = new Paginator;
        }
        return $this->paginator;
    }

    public function render($option1 = '', $option2 = 5)
    {
        $paginator = $this->getPaginator();
        $page = $paginator->page;
        if ($paginator->pageCount < 2) {
            $steps = [$page];
        } else {
            $arr = range(max($paginator->firstPage, $page - $option2), min($paginator->lastPage, $page + $option2));
            $count = 1;
            $quotient = ($paginator->pageCount - 1) / $count;
            for ($i = 0; $i <= $count; $i++) {
                $arr[] = round($quotient * $i) + $paginator->firstPage;
            }
            sort($arr);
            $steps = array_values(array_unique($arr));
        }
        $this->template->option1 = $option1;
        $this->template->option2 = $option2;
        $this->template->steps = $steps;
        $this->template->paginator = $paginator;
        $this->template->setFile(__DIR__ . '/bootstrap-paginator.latte');
        $this->template->render();
    }

    public function loadState(array $params): void
    {
        parent::loadState($params);
        $this->getPaginator()->page = $this->page;
    }
}
