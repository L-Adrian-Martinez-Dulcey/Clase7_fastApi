<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__, 2));
}

if (file_exists(APP_ROOT . '/.env')) {
    $lines = file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (is_array($lines)) {
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }
            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key . '=' . $value);
        }
    }
}

function env(string $key, mixed $default = null): mixed
{
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
}

if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '0');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_start();
}

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function respondJson(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function requireAuth(): void
{
    if (empty($_SESSION['user'])) {
        respondJson([
            'success' => false,
            'message' => 'No autorizado.',
        ], 401);
    }
}

function requireRole(array $allowedRoles): void
{
    requireAuth();
    $userRole = $_SESSION['user']['role_name'] ?? '';
    if (!in_array($userRole, $allowedRoles, true)) {
        respondJson([
            'success' => false,
            'message' => 'Permisos insuficientes.',
        ], 403);
    }
}

function isJsonRequest(): bool
{
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    return str_contains($contentType, 'application/json');
}
