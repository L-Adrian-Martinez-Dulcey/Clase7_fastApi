<?php
/**
 * EVORIA - Script de instalación (versión simplificada)
 * Ejecuta: php setup.php
 */

set_time_limit(300);

echo "================================\n";
echo "EVORIA - Setup Inicial\n";
echo "================================\n\n";

// Primero, crear la base de datos y el usuario
echo "[1/3] Conectando como root... ";
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;charset=utf8mb4',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ FALLO\n";
    echo "Error: " . $e->getMessage() . "\n";
    echo "\nAsegúrate de que MySQL está corriendo en XAMPP\n";
    exit(1);
}

// Crear base de datos y usuario
echo "[2/3] Creando base de datos y usuario... ";
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `evoria` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    $pdo->exec("CREATE USER IF NOT EXISTS 'evoria_user'@'localhost' IDENTIFIED BY 'evoria_password';");
    $pdo->exec("GRANT ALL PRIVILEGES ON evoria.* TO 'evoria_user'@'localhost';");
    $pdo->exec("FLUSH PRIVILEGES;");
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "⚠️ WARNING: " . $e->getMessage() . "\n";
    echo "Continuando...\n";
}

// Conectar con el nuevo usuario y ejecutar migraciones
echo "[3/3] Ejecutando migraciones... ";
try {
    $pdo = new PDO(
        'mysql:host=127.0.0.1;port=3306;dbname=evoria;charset=utf8mb4',
        'evoria_user',
        'evoria_password',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    
    // Leer y ejecutar SQL de migraciones
    $sqlFile = __DIR__ . '/database/migrations/001_create_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Archivo no encontrado: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt) && substr($stmt, 0, 2) !== '--'; }
    );
    
    foreach ($statements as $statement) {
        $pdo->exec($statement . ';');
    }
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ FALLO\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// Ejecutar seeders
echo "[4/4] Insertando datos de prueba... ";
try {
    $seedFile = __DIR__ . '/database/seeds/001_seed_roles_and_users.sql';
    if (file_exists($seedFile)) {
        $seedSql = file_get_contents($seedFile);
        $seedStatements = array_filter(
            array_map('trim', explode(';', $seedSql)),
            function($stmt) { return !empty($stmt) && substr($stmt, 0, 2) !== '--'; }
        );
        
        foreach ($seedStatements as $statement) {
            $pdo->exec($statement . ';');
        }
        echo "✅ OK\n";
    } else {
        echo "⚠️ SALTADO\n";
    }
} catch (Exception $e) {
    echo "⚠️ WARNING: " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "✅ Setup completado!\n";
echo "================================\n";
echo "\nAccede a: http://localhost/Evoria\n";
echo "\nCredenciales de prueba:\n";
echo "  Email: admin@evoria.com\n";
echo "  Contraseña: admin123\n\n";
echo "  Email: coord@evoria.com\n";
echo "  Contraseña: admin123\n\n";
echo "  Email: empleado@evoria.com\n";
echo "  Contraseña: admin123\n\n";
echo "  Email: proveedor@evoria.com\n";
echo "  Contraseña: admin123\n";
