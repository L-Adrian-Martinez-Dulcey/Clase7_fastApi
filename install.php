<?php
/**
 * EVORIA - Script de instalación
 * Ejecuta: php install.php
 */

set_time_limit(300);

echo "================================\n";
echo "EVORIA - Script de Instalación\n";
echo "================================\n\n";

// Cargar configuración
require_once __DIR__ . '/backend/config/bootstrap.php';

// 1. Verificar que .env existe
echo "[1/4] Verificando archivo .env... ";
if (!file_exists(APP_ROOT . '/.env')) {
    echo "❌ FALLO\n";
    echo "Crea el archivo .env basado en .env.example\n";
    exit(1);
}
echo "✅ OK\n";

// 2. Conectar a MySQL
echo "[2/4] Conectando a MySQL... ";
try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;port=%s;charset=utf8mb4',
            env('DB_HOST', '127.0.0.1'),
            env('DB_PORT', '3306')
        ),
        env('DB_USER', 'evoria_user'),
        env('DB_PASS', 'evoria_password'),
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ FALLO\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 3. Crear base de datos
echo "[3/4] Creando base de datos... ";
$dbName = env('DB_NAME', 'evoria');
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
    echo "✅ OK\n";
} catch (Exception $e) {
    echo "❌ FALLO\n";
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

// 4. Seleccionar base de datos y ejecutar migraciones
echo "[4/4] Ejecutando migraciones... ";
try {
    $pdo->exec("USE `$dbName`;");
    
    // Leer y ejecutar SQL de migraciones
    $sqlFile = APP_ROOT . '/database/migrations/001_create_tables.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("Archivo de migraciones no encontrado: $sqlFile");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Dividir por punto y coma y ejecutar cada sentencia
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

// 5. Ejecutar seeders
echo "[5/5] Insertando datos iniciales... ";
try {
    $pdo->exec("USE `$dbName`;");
    
    // Leer y ejecutar seeders
    $seedFile = APP_ROOT . '/database/seeds/001_seed_roles_and_users.sql';
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
        echo "⚠️ SALTADO (archivo no encontrado)\n";
    }
} catch (Exception $e) {
    echo "⚠️ ERROR (puede continuar): " . $e->getMessage() . "\n";
}

echo "\n================================\n";
echo "✅ Instalación completada!\n";
echo "================================\n";
echo "\nAccede a: http://localhost/Evoria\n";
echo "\nCredenciales de prueba:\n";
echo "  Email: admin@evoria.com\n";
echo "  Contraseña: admin123\n";
