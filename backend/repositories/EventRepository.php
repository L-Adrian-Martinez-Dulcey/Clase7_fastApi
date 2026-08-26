<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class EventRepository
{
    public function listAll(): array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->query(
            'SELECT e.*, u.name AS responsable_name
             FROM eventos e
             LEFT JOIN usuarios u ON u.id = e.responsable_id
             ORDER BY e.fecha_inicio DESC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'SELECT e.*, u.name AS responsable_name
             FROM eventos e
             LEFT JOIN usuarios u ON u.id = e.responsable_id
             WHERE e.id = :id LIMIT 1'
        );
        $stmt->execute(['id' => $id]);

        $event = $stmt->fetch();
        return $event ?: null;
    }

    public function create(array $data): int
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'INSERT INTO eventos (
                nombre, descripcion, fecha_inicio, fecha_fin, hora, ubicacion, tipo, estado, responsable_id
            ) VALUES (
                :nombre, :descripcion, :fecha_inicio, :fecha_fin, :hora, :ubicacion, :tipo, :estado, :responsable_id
            )'
        );

        $stmt->execute([
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'hora' => $data['hora'] ?? null,
            'ubicacion' => $data['ubicacion'] ?? null,
            'tipo' => $data['tipo'] ?? null,
            'estado' => $data['estado'] ?? 'Planificación',
            'responsable_id' => $data['responsable_id'] ?? null,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare(
            'UPDATE eventos SET
                nombre = :nombre,
                descripcion = :descripcion,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                hora = :hora,
                ubicacion = :ubicacion,
                tipo = :tipo,
                estado = :estado,
                responsable_id = :responsable_id,
                updated_at = CURRENT_TIMESTAMP
             WHERE id = :id'
        );

        return $stmt->execute([
            'id' => $id,
            'nombre' => $data['nombre'],
            'descripcion' => $data['descripcion'] ?? null,
            'fecha_inicio' => $data['fecha_inicio'],
            'fecha_fin' => $data['fecha_fin'],
            'hora' => $data['hora'] ?? null,
            'ubicacion' => $data['ubicacion'] ?? null,
            'tipo' => $data['tipo'] ?? null,
            'estado' => $data['estado'] ?? 'Planificación',
            'responsable_id' => $data['responsable_id'] ?? null,
        ]);
    }

    public function delete(int $id): bool
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('DELETE FROM eventos WHERE id = :id');
        return $stmt->execute(['id' => $id]);
    }
}
