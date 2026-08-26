<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class InventoryRepository
{
    public function listInventory(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT r.id AS recurso_id, r.nombre AS recurso_nombre, r.tipo,
                    r.cantidad_total, r.cantidad_disponible,
                    GREATEST(r.cantidad_total - r.cantidad_disponible, 0) AS cantidad_asignada,
                    CASE
                        WHEN r.cantidad_disponible <= 0 THEN 'critico'
                        WHEN r.cantidad_total > 0 AND r.cantidad_disponible <= (r.cantidad_total * 0.2) THEN 'bajo'
                        ELSE 'normal'
                    END AS estado
             FROM recursos r
             WHERE r.estado = 'activo'
             ORDER BY r.nombre ASC"
        );

        return $stmt->fetchAll();
    }
}
