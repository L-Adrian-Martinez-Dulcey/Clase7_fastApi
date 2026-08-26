<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/BudgetService.php';

class BudgetController
{
    public function __construct(private BudgetService $budgetService)
    {
    }

    public function list(): void
    {
        requireAuth();
        respondJson(['success' => true, 'data' => $this->budgetService->listBudgets()]);
    }

    public function save(): void
    {
        requireAuth();
        $payload = isJsonRequest() ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $result = $this->budgetService->saveBudget($payload ?? []);
        respondJson($result, $result['success'] ? 200 : 422);
    }
}
