<?php
$pdo = new PDO(
    'mysql:host=127.0.0.1;port=3306;dbname=evoria;charset=utf8mb4',
    'evoria_user',
    'evoria_password',
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$newHash = '$2y$10$403vm2yMGVUjexKGrhipr.qQG2bXwgrZ3zcM7b1wuLGqqg/PFwEL6';

$sql = "UPDATE usuarios SET password_hash = ? WHERE email IN (?, ?, ?, ?)";
$stmt = $pdo->prepare($sql);
$stmt->execute([
    $newHash,
    'admin@evoria.com',
    'coord@evoria.com',
    'empleado@evoria.com',
    'proveedor@evoria.com'
]);

echo "✅ Usuarios actualizados: " . $stmt->rowCount() . " filas\n";
echo "Ahora puedes iniciar sesión con:\n";
echo "  Email: admin@evoria.com\n";
echo "  Contraseña: admin123\n";
