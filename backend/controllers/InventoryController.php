<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/InventoryService.php';

class InventoryController
{
    public function __construct(private InventoryService $inventoryService)
    {
    }

    public function list(): void
    {
        requireAuth();
        respondJson(['success' => true, 'data' => $this->inventoryService->listInventory()]);
    }
}
