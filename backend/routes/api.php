<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/EventController.php';
require_once __DIR__ . '/../controllers/ResourceController.php';
require_once __DIR__ . '/../controllers/InventoryController.php';
require_once __DIR__ . '/../controllers/DashboardController.php';
require_once __DIR__ . '/../controllers/BudgetController.php';
require_once __DIR__ . '/../repositories/UserRepository.php';
require_once __DIR__ . '/../repositories/EventRepository.php';
require_once __DIR__ . '/../repositories/ResourceRepository.php';
require_once __DIR__ . '/../repositories/InventoryRepository.php';
require_once __DIR__ . '/../repositories/DashboardRepository.php';
require_once __DIR__ . '/../repositories/BudgetRepository.php';
require_once __DIR__ . '/../services/AuthService.php';
require_once __DIR__ . '/../services/EventService.php';
require_once __DIR__ . '/../services/ResourceService.php';
require_once __DIR__ . '/../services/InventoryService.php';
require_once __DIR__ . '/../services/DashboardService.php';
require_once __DIR__ . '/../services/BudgetService.php';

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$segments = array_values(array_filter(explode('/', $uri), 'strlen'));

$route = $_GET['route'] ?? ($segments[1] ?? '');

if (($segments[0] ?? '') === 'api' || !empty($_GET['route'])) {
    $authService = new AuthService(new UserRepository());
    $authController = new AuthController($authService);
    $eventService = new EventService(new EventRepository());
    $eventController = new EventController($eventService);
    $resourceService = new ResourceService(new ResourceRepository());
    $resourceController = new ResourceController($resourceService);
    $inventoryController = new InventoryController(new InventoryService(new InventoryRepository()));
    $dashboardController = new DashboardController(new DashboardService(new DashboardRepository()));
    $budgetController = new BudgetController(new BudgetService(new BudgetRepository()));

    if ($method === 'GET' && $route === 'dashboard/resumen') {
        $dashboardController->summary();
    }

    if ($method === 'GET' && $route === 'presupuestos') {
        $budgetController->list();
    }

    if ($method === 'POST' && $route === 'presupuestos') {
        $budgetController->save();
    }

    if ($method === 'POST' && $route === 'login') {
        $authController->login();
    }

    if ($method === 'POST' && $route === 'logout') {
        $authController->logout();
    }

    if ($method === 'GET' && $route === 'me') {
        $authController->me();
    }

    if ($method === 'GET' && $route === 'eventos') {
        $eventController->list();
    }

    if ($method === 'GET' && preg_match('/^eventos\/(\d+)$/', $route, $matches)) {
        $eventController->show((int) $matches[1]);
    }

    if ($method === 'POST' && $route === 'eventos') {
        $eventController->create();
    }

    if ($method === 'PUT' && preg_match('/^eventos\/(\d+)$/', $route, $matches)) {
        $_PUT = isJsonRequest() ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $_POST = $_PUT;
        $eventController->update((int) $matches[1]);
    }

    if ($method === 'DELETE' && preg_match('/^eventos\/(\d+)$/', $route, $matches)) {
        $eventController->delete((int) $matches[1]);
    }

    if ($method === 'GET' && $route === 'recursos') {
        $resourceController->list();
    }

    if ($method === 'GET' && preg_match('/^recursos\/(\d+)$/', $route, $matches)) {
        $resourceController->show((int) $matches[1]);
    }

    if ($method === 'POST' && $route === 'recursos') {
        $resourceController->create();
    }

    if ($method === 'PUT' && preg_match('/^recursos\/(\d+)$/', $route, $matches)) {
        $_PUT = isJsonRequest() ? json_decode(file_get_contents('php://input'), true) : $_POST;
        $_POST = $_PUT;
        $resourceController->update((int) $matches[1]);
    }

    if ($method === 'DELETE' && preg_match('/^recursos\/(\d+)$/', $route, $matches)) {
        $resourceController->delete((int) $matches[1]);
    }

    if ($method === 'GET' && $route === 'inventario') {
        $inventoryController->list();
    }

    respondJson(['success' => false, 'message' => 'Endpoint no encontrado.'], 404);
}

respondJson(['success' => false, 'message' => 'Ruta no encontrada.'], 404);
