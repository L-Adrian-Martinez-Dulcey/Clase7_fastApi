<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/EventService.php';

class EventController
{
    public function __construct(private EventService $eventService)
    {
    }

    public function list(): void
    {
        requireAuth();
        respondJson(['success' => true, 'data' => $this->eventService->listEvents()]);
    }

    public function show(int $id): void
    {
        requireAuth();
        $event = $this->eventService->findEvent($id);
        if (!$event) {
            respondJson(['success' => false, 'message' => 'Evento no encontrado.'], 404);
        }

        respondJson(['success' => true, 'data' => $event]);
    }

    public function create(): void
    {
        requireAuth();
        $payload = isJsonRequest() ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $result = $this->eventService->createEvent($payload ?? []);

        if (!$result['success']) {
            respondJson(['success' => false, 'message' => $result['errors'][0] ?? 'Datos inválidos.'], 422);
        }

        respondJson(['success' => true, 'message' => $result['message'], 'id' => $result['id']]);
    }

    public function update(int $id): void
    {
        requireAuth();
        $payload = isJsonRequest() ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $result = $this->eventService->updateEvent($id, $payload ?? []);

        if (!$result['success']) {
            respondJson(['success' => false, 'message' => $result['message'] ?? 'No se pudo actualizar.'], 422);
        }

        respondJson(['success' => true, 'message' => $result['message']]);
    }

    public function delete(int $id): void
    {
        requireAuth();
        $result = $this->eventService->deleteEvent($id);
        if (!$result['success']) {
            respondJson(['success' => false, 'message' => $result['message']], 400);
        }

        respondJson(['success' => true, 'message' => $result['message']]);
    }
}
