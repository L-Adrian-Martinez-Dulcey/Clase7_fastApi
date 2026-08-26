<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/InventoryRepository.php';

class InventoryService
{
    public function __construct(private InventoryRepository $inventoryRepository)
    {
    }

    public function listInventory(): array
    {
        $items = $this->inventoryRepository->listInventory();
        $summary = ['resources_available' => 0, 'resources_assigned' => 0, 'total_quantity' => 0, 'available_quantity' => 0];

        foreach ($items as $item) {
            $summary['resources_available'] += (int) $item['cantidad_disponible'] > 0 ? 1 : 0;
            $summary['resources_assigned'] += (int) $item['cantidad_asignada'] > 0 ? 1 : 0;
            $summary['total_quantity'] += (int) $item['cantidad_total'];
            $summary['available_quantity'] += (int) $item['cantidad_disponible'];
        }

        return ['items' => $items, 'summary' => $summary];
    }
}
