<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/EventRepository.php';

class EventService
{
    public function __construct(private EventRepository $eventRepository)
    {
    }

    public function listEvents(): array
    {
        return $this->eventRepository->listAll();
    }

    public function findEvent(int $id): ?array
    {
        return $this->eventRepository->findById($id);
    }

    public function createEvent(array $data): array
    {
        $errors = $this->validateEvent($data);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $eventId = $this->eventRepository->create($data);
        return ['success' => true, 'id' => $eventId, 'message' => 'Evento creado correctamente.'];
    }

    public function updateEvent(int $id, array $data): array
    {
        $errors = $this->validateEvent($data);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $updated = $this->eventRepository->update($id, $data);
        return ['success' => $updated, 'message' => $updated ? 'Evento actualizado.' : 'No se pudo actualizar el evento.'];
    }

    public function deleteEvent(int $id): array
    {
        $deleted = $this->eventRepository->delete($id);
        return ['success' => $deleted, 'message' => $deleted ? 'Evento eliminado.' : 'No se pudo eliminar el evento.'];
    }

    public function validateEvent(array $data): array
    {
        $errors = [];

        $nombre = trim((string) ($data['nombre'] ?? ''));
        $fechaInicio = trim((string) ($data['fecha_inicio'] ?? ''));
        $fechaFin = trim((string) ($data['fecha_fin'] ?? ''));

        if ($nombre === '') {
            $errors[] = 'El nombre del evento es obligatorio.';
        }

        if ($fechaInicio === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
            $errors[] = 'La fecha de inicio es inválida.';
        }

        if ($fechaFin === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)) {
            $errors[] = 'La fecha de finalización es inválida.';
        }

        if ($fechaInicio !== '' && $fechaFin !== '' && $fechaFin < $fechaInicio) {
            $errors[] = 'La fecha de finalización no puede ser anterior a la fecha de inicio.';
        }

        return $errors;
    }
}
