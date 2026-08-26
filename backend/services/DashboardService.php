<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/DashboardRepository.php';

class DashboardService
{
    public function __construct(private DashboardRepository $dashboardRepository)
    {
    }

    public function getSummary(): array
    {
        return $this->dashboardRepository->getSummary();
    }
}
