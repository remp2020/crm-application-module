<?php

namespace Crm\ApplicationModule\Components;

use Nette\Application\UI\Control;
use Nette\Utils\Paginator;

class PreviousNextPaginator extends Control
{
    /** @var Paginator */
    private $paginator;

    private ?int $actualItemCount = null;

    /** @persistent */
    public int $page = 1;

    public function render()
    {
        $paginator = $this->paginator;

        $this->template->isLastPage = $this->actualItemCount !== null && $this->actualItemCount < $this->getPaginator()->getItemsPerPage();
        $this->template->paginator = $paginator;
        $this->template->setFile(__DIR__ . '/previous-next-paginator.latte');
        $this->template->render();
    }

    public function getPaginator(): Paginator
    {
        if (!$this->paginator) {
            $this->paginator = new Paginator;
        }
        return $this->paginator;
    }

    public function loadState(array $params): void
    {
        parent::loadState($params);
        $this->getPaginator()->page = $this->page;
    }

    public function setActualItemCount(int $count): void
    {
        $this->actualItemCount = $count;
    }
}
