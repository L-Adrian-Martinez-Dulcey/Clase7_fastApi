<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class BudgetRepository
{
    public function listAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            'SELECT p.*, e.nombre AS evento_nombre
             FROM presupuestos p
             INNER JOIN eventos e ON e.id = p.evento_id
             ORDER BY p.updated_at DESC, p.id DESC'
        );

        return $stmt->fetchAll();
    }

    public function eventExists(int $eventId): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT 1 FROM eventos WHERE id = :id');
        $stmt->execute(['id' => $eventId]);
        return (bool) $stmt->fetchColumn();
    }

    public function save(array $data): array
    {
        $pdo = Database::getConnection();
        $find = $pdo->prepare('SELECT id FROM presupuestos WHERE evento_id = :evento_id ORDER BY id ASC LIMIT 1');
        $find->execute(['evento_id' => $data['evento_id']]);
        $id = $find->fetchColumn();

        if ($id !== false) {
            $stmt = $pdo->prepare(
                'UPDATE presupuestos SET subtotal_recursos = :subtotal_recursos, costos_adicionales = :costos_adicionales, total = :total, moneda = :moneda, updated_at = CURRENT_TIMESTAMP WHERE id = :id'
            );
            $stmt->execute([
                'subtotal_recursos' => $data['subtotal_recursos'],
                'costos_adicionales' => $data['costos_adicionales'],
                'total' => $data['total'],
                'moneda' => $data['moneda'],
                'id' => $id,
            ]);
            return ['id' => (int) $id, 'updated' => true];
        }

        $stmt = $pdo->prepare(
            'INSERT INTO presupuestos (evento_id, subtotal_recursos, costos_adicionales, total, moneda) VALUES (:evento_id, :subtotal_recursos, :costos_adicionales, :total, :moneda)'
        );
        $stmt->execute([
            'evento_id' => $data['evento_id'],
            'subtotal_recursos' => $data['subtotal_recursos'],
            'costos_adicionales' => $data['costos_adicionales'],
            'total' => $data['total'],
            'moneda' => $data['moneda'],
        ]);
        return ['id' => (int) $pdo->lastInsertId(), 'updated' => false];
    }
}
