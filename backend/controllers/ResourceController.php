<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/ResourceService.php';

class ResourceController
{
    public function __construct(private ResourceService $resourceService)
    {
    }

    public function list(): void
    {
        requireAuth();
        respondJson(['success' => true, 'data' => $this->resourceService->listResources()]);
    }

    public function show(int $id): void
    {
        requireAuth();
        $resource = $this->resourceService->findResource($id);
        if (!$resource) {
            respondJson(['success' => false, 'message' => 'Recurso no encontrado.'], 404);
        }

        respondJson(['success' => true, 'data' => $resource]);
    }

    public function create(): void
    {
        requireAuth();
        $payload = isJsonRequest() ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $result = $this->resourceService->createResource($payload ?? []);

        if (!$result['success']) {
            respondJson(['success' => false, 'message' => $result['errors'][0] ?? 'Datos inválidos.'], 422);
        }

        respondJson(['success' => true, 'message' => $result['message'], 'id' => $result['id']]);
    }

    public function update(int $id): void
    {
        requireAuth();
        $payload = isJsonRequest() ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $result = $this->resourceService->updateResource($id, $payload ?? []);

        if (!$result['success']) {
            respondJson(['success' => false, 'message' => $result['message'] ?? 'No se pudo actualizar.'], 422);
        }

        respondJson(['success' => true, 'message' => $result['message']]);
    }

    public function delete(int $id): void
    {
        requireAuth();
        $result = $this->resourceService->deleteResource($id);
        if (!$result['success']) {
            respondJson(['success' => false, 'message' => $result['message']], 400);
        }

        respondJson(['success' => true, 'message' => $result['message']]);
    }
}
