<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class DashboardRepository
{
    public function getSummary(): array
    {
        $pdo = Database::getConnection();

        $eventsByStatus = $pdo->query('SELECT estado, COUNT(*) AS total FROM eventos GROUP BY estado ORDER BY estado ASC')->fetchAll();
        $budgetByEvent = $pdo->query('SELECT e.nombre AS label, p.total FROM presupuestos p INNER JOIN eventos e ON e.id = p.evento_id ORDER BY p.total DESC, e.nombre ASC')->fetchAll();

        return [
            'metrics' => [
                'upcoming_events' => (int) $pdo->query('SELECT COUNT(*) FROM eventos WHERE fecha_inicio >= CURDATE()')->fetchColumn(),
                'active_events' => (int) $pdo->query("SELECT COUNT(*) FROM eventos WHERE estado NOT IN ('Finalizado', 'Cancelado')")->fetchColumn(),
                'available_resources' => (int) $pdo->query("SELECT COALESCE(SUM(cantidad_disponible), 0) FROM recursos WHERE estado = 'activo'")->fetchColumn(),
                'pending_alerts' => (int) $pdo->query("SELECT COUNT(*) FROM alertas WHERE estado = 'pendiente'")->fetchColumn(),
                'total_budget' => (float) $pdo->query('SELECT COALESCE(SUM(total), 0) FROM presupuestos')->fetchColumn(),
                'low_inventory' => (int) $pdo->query("SELECT COUNT(*) FROM inventario WHERE estado <> 'normal'")->fetchColumn(),
                'upcoming_activities' => (int) $pdo->query("SELECT COUNT(*) FROM cronogramas WHERE fecha_inicio >= NOW() AND estado <> 'cancelado'")->fetchColumn(),
            ],
            'events_by_status' => $eventsByStatus,
            'budget_by_event' => $budgetByEvent,
        ];
    }
}
