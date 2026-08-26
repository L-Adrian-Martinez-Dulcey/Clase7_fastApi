<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class ResourceRepository
{
    public function listAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query('SELECT * FROM recursos ORDER BY nombre ASC');
        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT * FROM recursos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $resource = $stmt->fetch();
        return $resource ?: null;
    }

    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO recursos (nombre, tipo, descripcion, costo, cantidad_total, cantidad_disponible, disponibilidad, estado)
             VALUES (:nombre, :tipo, :descripcion, :costo, :cantidad_total, :cantidad_disponible, :disponibilidad, :estado)'
        );

        $stmt->execute([
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'],
            'descripcion' => $data['descripcion'] ?? null,
            'costo' => (float) ($data['costo'] ?? 0),
            'cantidad_total' => (int) ($data['cantidad_total'] ?? 0),
            'cantidad_disponible' => (int) ($data['cantidad_disponible'] ?? $data['cantidad_total'] ?? 0),
            'disponibilidad' => $data['disponibilidad'] ?? 'disponible',
            'estado' => $data['estado'] ?? 'activo',
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'UPDATE recursos SET
                nombre = :nombre,
                tipo = :tipo,
                descripcion = :descripcion,
                costo = :costo,
                cantidad_total = :cantidad_total,
                cantidad_disponible = :cantidad_disponible,
                disponibilidad = :disponibilidad,
                estado = :estado,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'tipo' => $data['tipo'],
            'descripcion' => $data['descripcion'] ?? null,
            'costo' => (float) ($data['costo'] ?? 0),
            'cantidad_total' => (int) ($data['cantidad_total'] ?? 0),
            'cantidad_disponible' => (int) ($data['cantidad_disponible'] ?? 0),
            'disponibilidad' => $data['disponibilidad'] ?? 'disponible',
            'estado' => $data['estado'] ?? 'activo',
        ]);
    }

    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM recursos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
