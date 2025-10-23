<?php

declare(strict_types=1);

namespace Crm\ApplicationModule\Components\AjaxDataPaginator;

/**
 * Interface for components that implement pagination functionality.
 *
 * Components implementing this interface can use the PaginatesDataTrait
 * to get full pagination support with AJAX handling automatically.
 *
 * @package Crm\ApplicationModule\Components
 */
interface PaginatedComponent
{
    /**
     * Handle pagination page change signal.
     */
    public function handlePageChange(
        string $snippetName,
        int $entityId,
        int $page = 1,
    ): void;
    
    
    /**
     * Get the ID of the data entity being paginated.
     * This is typically a user ID, category ID, etc.
     */
    public function getEntityId(): int;
}
