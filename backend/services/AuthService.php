<?php

declare(strict_types=1);

require_once __DIR__ . '/../repositories/UserRepository.php';

class AuthService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function login(string $email, string $password): array
    {
        $user = $this->userRepository->findByEmail($email);
        if (!$user) {
            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        if (!empty($user['locked_until']) && new DateTime($user['locked_until']) > new DateTime()) {
            return ['success' => false, 'message' => 'Cuenta bloqueada temporalmente.'];
        }

        if (!password_verify($password, $user['password_hash'])) {
            $newAttempts = (int) $user['login_attempts'] + 1;
            $lockedUntil = null;
            if ($newAttempts >= (int) env('MAX_LOGIN_ATTEMPTS', 5)) {
                $lockedUntil = (new DateTime())->modify('+' . (int) env('LOCKOUT_MINUTES', 15) . ' minutes')->format('Y-m-d H:i:s');
            }

            $pdo = Database::getConnection();
            $stmt = $pdo->prepare('UPDATE usuarios SET login_attempts = :attempts, locked_until = :locked_until WHERE id = :id');
            $stmt->execute(['attempts' => $newAttempts, 'locked_until' => $lockedUntil, 'id' => $user['id']]);

            return ['success' => false, 'message' => 'Credenciales inválidas.'];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE usuarios SET login_attempts = 0, locked_until = NULL WHERE id = :id');
        $stmt->execute(['id' => $user['id']]);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role_name' => $user['role_name'],
        ];

        return ['success' => true, 'user' => $_SESSION['user'], 'message' => 'Inicio de sesión exitoso.'];
    }

    public function logout(): void
    {
        unset($_SESSION['user']);
        session_destroy();
    }
}
