<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Components\AjaxDataPaginator;

use LogicException;
use Nette\Application\Attributes\Persistent;

/**
 * Provides complete pagination infrastructure for components.
 *
 * Components using this trait get paginator API and AJAX handling.
 * Must be used with PaginatedComponent interface.
 *
 * @package Crm\ApplicationModule\Components
 */
trait PaginatesDataTrait
{
    /**
     * Page numbers for multiple paginators.
     * Keyed by snippet name.
     */
    #[Persistent]
    public array $pages = [];

    /**
     * Entity ID for which we're paginating data.
     * Automatically persisted across requests.
     */
    #[Persistent]
    public ?int $entityId = null;

    
    /**
     * Handle pagination page change signal.
     *
     * This method coordinates both data and pagination UI updates,
     * handling both AJAX and non-AJAX requests appropriately.
     *
     */
    public function handlePageChange(
        string $snippetName,
        int $entityId,
        int $page = 1,
    ): void {
        $page = max(1, $page);
        $this->pages[$snippetName] = $page;
        $paginatorName = 'paginator_' . $snippetName;
        
        // Update the specific paginator
        if (isset($this[$paginatorName])) {
            $this[$paginatorName]->setPage($page);
        }
        
        // For non-AJAX requests, redirect to prevent form resubmission
        if (!$this->presenter->isAjax()) {
            $this->presenter->redirect('this');
        }

        // Store entityId for rendering
        $this->entityId = $entityId;

        // Mark the specific snippets for redraw
        $this->redrawControl($snippetName);

        // Set snippet mode to render only marked snippets
        $this->snippetMode = true;

        // Call render to generate content for marked snippets
        // When snippetMode is true, only marked snippets are rendered and sent
        $this->render($entityId);

        $this->snippetMode = false;

        // Send the rendered snippets to the browser
        $this->presenter->sendPayload();
    }
    
    /**
     * Returns the entity ID for which we're paginating data.
     */
    public function getEntityId(): int
    {
        if ($this->entityId === null) {
            throw new LogicException('Entity ID not set. Call render() with entity ID first.');
        }
        
        return $this->entityId;
    }
    

    /**
     * Get or create an AJAX paginator for the specified snippet.
     *
     * This is the preferred way to create paginators. It automatically handles
     * component registration and initial page setup.
     *
     * @param string $snippetName Name of the snippet that wraps the paginated content
     * @param int $itemCount Total number of items to paginate
     * @param int $itemsPerPage Number of items per page
     * @param int $pagesToShow Number of page links to show
     * @return AjaxDataPaginator The paginator instance configured for this snippet
     */
    protected function getAjaxPaginator(
        string $snippetName,
        int $itemCount,
        int $itemsPerPage = 12,
        int $pagesToShow = 5,
    ): AjaxDataPaginator {
        $componentName = 'paginator_' . $snippetName;

        if (isset($this[$componentName])) {
            return $this[$componentName];
        }

        $paginator = new AjaxDataPaginator(
            snippetName: $snippetName,
            itemCount: $itemCount,
            itemsPerPage: $itemsPerPage,
            pagesToShow: $pagesToShow,
        );

        // Set initial page if stored
        $page = $this->pages[$snippetName] ?? 1;
        $paginator->setPage($page);

        $this->addComponent($paginator, $componentName);

        return $this[$componentName];
    }
}
