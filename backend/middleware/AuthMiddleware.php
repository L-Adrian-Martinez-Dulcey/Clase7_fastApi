<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';

class AuthMiddleware
{
    public static function requireLogin(): void
    {
        if (empty($_SESSION['user'])) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'No autorizado.',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function requireRole(array $roles): void
    {
        self::requireLogin();

        $userRole = $_SESSION['user']['role_name'] ?? '';
        if (!in_array($userRole, $roles, true)) {
            http_response_code(403);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => false,
                'message' => 'Permisos insuficientes.',
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    public static function csrfCheck(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            if (empty($_SESSION['csrf_token']) || $_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) {
                http_response_code(419);
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success' => false,
                    'message' => 'Token CSRF inválido.',
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
    }
}
