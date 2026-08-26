<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

$env = function (string $key, mixed $default = null): mixed {
    return $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key) ?? $default;
};

return [
    'app_name' => $env('APP_NAME', 'EVORIA'),
    'app_url' => $env('APP_URL', 'http://localhost'),
    'session_lifetime' => (int) $env('SESSION_LIFETIME', 3600),
    'max_login_attempts' => (int) $env('MAX_LOGIN_ATTEMPTS', 5),
    'lockout_minutes' => (int) $env('LOCKOUT_MINUTES', 15),
];
