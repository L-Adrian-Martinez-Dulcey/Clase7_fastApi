<?php
// Script para verificar y generar hashes de contraseña

$password = 'admin123';
$hashedPassword = '$2y$10$M3FX8vB5L5JHxv0Gg6t7Vu7I0mpH3JTjPrlVwthS8mN.Rns1ES2T6';

echo "Verificando hash existente...\n";
if (password_verify($password, $hashedPassword)) {
    echo "✅ El hash es correcto para 'admin123'\n";
} else {
    echo "❌ El hash NO es correcto para 'admin123'\n";
}

echo "\nGenerando nuevo hash para 'admin123'...\n";
$newHash = password_hash('admin123', PASSWORD_BCRYPT);
echo "Hash: " . $newHash . "\n";

echo "\nVerificando nuevo hash...\n";
if (password_verify('admin123', $newHash)) {
    echo "✅ El nuevo hash es correcto\n";
} else {
    echo "❌ El nuevo hash NO es correcto\n";
}

echo "\n\nScript SQL para actualizar los usuarios:\n";
echo "UPDATE usuarios SET password_hash = '" . $newHash . "' WHERE email IN ('admin@evoria.com', 'coord@evoria.com', 'empleado@evoria.com', 'proveedor@evoria.com');\n";
