<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/ResourceRepository.php';

class ResourceService
{
    public function __construct(private ResourceRepository $resourceRepository)
    {
    }

    public function listResources(): array
    {
        return $this->resourceRepository->listAll();
    }

    public function findResource(int $id): ?array
    {
        return $this->resourceRepository->findById($id);
    }

    public function createResource(array $data): array
    {
        $errors = $this->validateResource($data);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $id = $this->resourceRepository->create($data);
        return ['success' => true, 'id' => $id, 'message' => 'Recurso creado correctamente.'];
    }

    public function updateResource(int $id, array $data): array
    {
        $errors = $this->validateResource($data);
        if ($errors !== []) {
            return ['success' => false, 'errors' => $errors];
        }

        $updated = $this->resourceRepository->update($id, $data);
        return ['success' => $updated, 'message' => $updated ? 'Recurso actualizado.' : 'No se pudo actualizar el recurso.'];
    }

    public function deleteResource(int $id): array
    {
        $deleted = $this->resourceRepository->delete($id);
        return ['success' => $deleted, 'message' => $deleted ? 'Recurso eliminado.' : 'No se pudo eliminar el recurso.'];
    }

    private function validateResource(array $data): array
    {
        $errors = [];

        $nombre = trim((string) ($data['nombre'] ?? ''));
        $tipo = trim((string) ($data['tipo'] ?? ''));
        $costo = (float) ($data['costo'] ?? 0);
        $cantidad = (int) ($data['cantidad_total'] ?? 0);

        if ($nombre === '') {
            $errors[] = 'El nombre del recurso es obligatorio.';
        }

        if ($tipo === '') {
            $errors[] = 'El tipo del recurso es obligatorio.';
        }

        if ($costo < 0) {
            $errors[] = 'El costo no puede ser negativo.';
        }

        if ($cantidad < 0) {
            $errors[] = 'La cantidad total no puede ser negativa.';
        }

        return $errors;
    }
}
