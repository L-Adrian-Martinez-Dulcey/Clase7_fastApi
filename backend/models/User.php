<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class User extends BaseModel
{
    public static function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM usuarios u INNER JOIN roles r ON r.id = u.role_id WHERE u.email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function updateLoginAttempts(int $userId, int $attempts, ?string $lockedUntil = null): void
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('UPDATE usuarios SET login_attempts = :attempts, locked_until = :locked_until WHERE id = :id');
        $stmt->execute([
            'attempts' => $attempts,
            'locked_until' => $lockedUntil,
            'id' => $userId,
        ]);
    }
}
