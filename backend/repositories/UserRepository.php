<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

class UserRepository
{
    public function findByEmail(string $email): ?array
    {
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT u.*, r.name AS role_name FROM usuarios u INNER JOIN roles r ON r.id = u.role_id WHERE u.email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        return $stmt->fetch() ?: null;
    }
}
