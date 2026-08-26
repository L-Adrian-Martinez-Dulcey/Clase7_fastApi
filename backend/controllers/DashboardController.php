<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/DashboardService.php';

class DashboardController
{
    public function __construct(private DashboardService $dashboardService)
    {
    }

    public function summary(): void
    {
        requireAuth();
        respondJson(['success' => true, 'data' => $this->dashboardService->getSummary()]);
    }
}
