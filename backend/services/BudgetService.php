<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/BudgetRepository.php';

class BudgetService
{
    public function __construct(private BudgetRepository $budgetRepository)
    {
    }

    public function listBudgets(): array
    {
        return $this->budgetRepository->listAll();
    }

    public function saveBudget(array $data): array
    {
        $eventId = filter_var($data['evento_id'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $subtotal = filter_var($data['subtotal_recursos'] ?? null, FILTER_VALIDATE_FLOAT);
        $additional = filter_var($data['costos_adicionales'] ?? null, FILTER_VALIDATE_FLOAT);
        $currency = strtoupper(trim((string) ($data['moneda'] ?? 'COP')));

        if ($eventId === false || $eventId === null) return ['success' => false, 'message' => 'Selecciona un evento válido.'];
        if ($subtotal === false || $subtotal < 0 || $additional === false || $additional < 0) return ['success' => false, 'message' => 'Los valores del presupuesto deben ser números positivos.'];
        if (!preg_match('/^[A-Z]{3,10}$/', $currency)) return ['success' => false, 'message' => 'La moneda debe usar entre 3 y 10 letras mayúsculas.'];
        if (!$this->budgetRepository->eventExists((int) $eventId)) return ['success' => false, 'message' => 'El evento seleccionado no existe.'];

        $saved = $this->budgetRepository->save([
            'evento_id' => (int) $eventId,
            'subtotal_recursos' => round((float) $subtotal, 2),
            'costos_adicionales' => round((float) $additional, 2),
            'total' => round((float) $subtotal + (float) $additional, 2),
            'moneda' => $currency,
        ]);

        return ['success' => true, 'id' => $saved['id'], 'message' => $saved['updated'] ? 'Presupuesto actualizado correctamente.' : 'Presupuesto creado correctamente.'];
    }
}
