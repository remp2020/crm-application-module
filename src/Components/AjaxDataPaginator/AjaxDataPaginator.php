<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Components\AjaxDataPaginator;

use LogicException;
use Nette\Application\UI\Control;
use Nette\Utils\Paginator;

/**
 * Renders pagination UI controls with AJAX support.
 *
 * Page state is managed by the parent component via PaginatesDataTrait.
 *
 * @package Crm\ApplicationModule\Components
 */
class AjaxDataPaginator extends Control
{
    private static bool $assetsRendered = false;

    private readonly Paginator $paginator;

    public function __construct(
        private readonly string $snippetName,
        private readonly int $itemCount,
        private readonly int $itemsPerPage,
        private readonly int $pagesToShow,
    ) {
        $this->paginator = new Paginator();
        $this->paginator->setItemsPerPage($this->itemsPerPage);
        $this->paginator->setItemCount($this->itemCount);
    }

    /**
     * Get the current page number.
     */
    public function getPage(): int
    {
        return $this->paginator->getPage();
    }

    /**
     * Get the offset for database queries.
     */
    public function getOffset(): int
    {
        return $this->paginator->getOffset();
    }

    /**
     * Get the limit for database queries (items per page).
     */
    public function getLimit(): int
    {
        return $this->paginator->getItemsPerPage();
    }

    /**
     * Calculate the page range for pagination display.
     *
     * @return array{startPage: int, endPage: int}
     */
    private function calculatePageRange(int $currentPage, int $pageCount): array
    {
        $pagesToShow = $this->pagesToShow;
        $halfRange = floor($pagesToShow / 2);
        
        $startPage = max(1, $currentPage - $halfRange);
        $endPage = min($pageCount, $currentPage + $halfRange);
        
        // Adjust range if at boundaries
        if ($startPage === 1) {
            $endPage = min($pageCount, $pagesToShow);
        }
        if ($endPage === $pageCount) {
            $startPage = max(1, $pageCount - $pagesToShow + 1);
        }
        
        return ['startPage' => $startPage, 'endPage' => $endPage];
    }



    /**
     * Generate pagination link to parent component's signal handler.
     */
    public function getPageLink(int $targetPage): string
    {
        $parent = $this->getParent();
        if (!$parent instanceof PaginatedComponent) {
            throw new LogicException('AjaxDataPaginator must be used within a PaginatedComponent');
        }
        
        /** @var PaginatedComponent&Control $parent */
        $params = [
            'page' => $targetPage,
            'entityId' => $parent->getEntityId(),
        ];
        
        // Add snippet name for multiple paginator support
        $params['snippetName'] = $this->snippetName;
        
        return $parent->link('pageChange!', $params);
    }
    
    public function render(): void
    {
        $paginator = $this->paginator;
        
        // Early return if no pagination needed
        if ($paginator->getPageCount() <= 1) {
            return;
        }
        
        $currentPage = 1;
        $parent = $this->getParent();

        // Get current page from parent's state.
        // The 'pages' property is provided by PaginatesDataTrait.
        if ($parent && property_exists($parent, 'pages')) {
            $currentPage = $parent->pages[$this->snippetName] ?? 1;
        }

        // Set page and read it back to get the validated value.
        // The paginator clamps the page to valid bounds (1 to pageCount),
        // ensuring we never work with invalid page numbers.
        $paginator->setPage($currentPage);
        $currentPage = $paginator->getPage();
        
        $pageCount = $paginator->getPageCount();
        
        $pageRange = $this->calculatePageRange($currentPage, $pageCount);
        $startPage = $pageRange['startPage'];
        $endPage = $pageRange['endPage'];
        
        $this->template->page = $currentPage;
        $this->template->startPage = $startPage;
        $this->template->endPage = $endPage;
        $this->template->pageCount = $pageCount;
        
        $this->template->getLinkCallback = function (int $targetPage): string {
            return $this->getPageLink($targetPage);
        };
        
        $this->template->hasPreviousPage = !$paginator->isFirst();
        $this->template->hasNextPage = !$paginator->isLast();
        
        // Ensure CSS/JS assets render only once per page
        $this->template->renderAssets = !self::$assetsRendered;
        if (!self::$assetsRendered) {
            self::$assetsRendered = true;
        }
        
        $this->template->setFile(__DIR__ . '/ajax_data_paginator.latte');
        $this->template->render();
    }
    
    /**
     * Sync the current page with an external value.
     * Useful when parent component manages the page state.
     */
    public function setPage(int $page): self
    {
        $this->paginator->setPage($page);
        return $this;
    }
    
    /**
     * Get the total number of pages.
     */
    public function getPageCount(): int
    {
        return max(1, $this->paginator->getPageCount());
    }
}
