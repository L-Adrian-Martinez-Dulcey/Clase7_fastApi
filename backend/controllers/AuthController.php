<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/bootstrap.php';
require_once __DIR__ . '/../services/AuthService.php';

class AuthController
{
    public function __construct(private AuthService $authService)
    {
    }

    public function login(): void
    {
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $email = trim((string) ($input['email'] ?? ''));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || $password === '') {
            respondJson(['success' => false, 'message' => 'Email y contraseña requeridos.'], 400);
        }

        $result = $this->authService->login($email, $password);
        if ($result['success']) {
            respondJson($result, 200);
        }

        respondJson($result, 401);
    }

    public function logout(): void
    {
        requireAuth();
        $this->authService->logout();
        respondJson(['success' => true, 'message' => 'Sesión cerrada.']);
    }

    public function me(): void
    {
        requireAuth();
        respondJson(['success' => true, 'user' => $_SESSION['user']]);
    }
}
