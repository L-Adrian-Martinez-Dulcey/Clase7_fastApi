# 🔧 GUÍA DE IMPLEMENTACIÓN - MEJORAS RECOMENDADAS

---

## 1. VALIDADOR DE ENTRADA (CRÍTICO)

### Crear: `backend/helpers/Validator.php`

```php
<?php

declare(strict_types=1);

class Validator
{
    private array $errors = [];

    public static function make(array $data, array $rules): self
    {
        return new self($data, $rules);
    }

    public function __construct(private array $data, private array $rules)
    {
        $this->validate();
    }

    private function validate(): void
    {
        foreach ($this->rules as $field => $ruleString) {
            $rules = explode('|', $ruleString);
            $value = $this->data[$field] ?? null;

            foreach ($rules as $rule) {
                if (str_contains($rule, ':')) {
                    [$ruleName, $param] = explode(':', $rule);
                } else {
                    $ruleName = $rule;
                    $param = null;
                }

                $this->executeRule($field, $value, $ruleName, $param);
            }
        }
    }

    private function executeRule(string $field, mixed $value, string $rule, ?string $param): void
    {
        match ($rule) {
            'required' => $this->validateRequired($field, $value),
            'email' => $this->validateEmail($field, $value),
            'min' => $this->validateMin($field, $value, $param),
            'max' => $this->validateMax($field, $value, $param),
            'regex' => $this->validateRegex($field, $value, $param),
            'confirmed' => $this->validateConfirmed($field, $value),
            default => null,
        };
    }

    private function validateRequired(string $field, mixed $value): void
    {
        if (empty($value) || (is_string($value) && trim($value) === '')) {
            $this->errors[$field] = "El campo {$field} es requerido.";
        }
    }

    private function validateEmail(string $field, mixed $value): void
    {
        if (!empty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "El campo {$field} debe ser un email válido.";
        }
    }

    private function validateMin(string $field, mixed $value, ?string $min): void
    {
        if (!empty($value) && strlen((string) $value) < (int) $min) {
            $this->errors[$field] = "El campo {$field} debe tener mínimo {$min} caracteres.";
        }
    }

    private function validateMax(string $field, mixed $value, ?string $max): void
    {
        if (!empty($value) && strlen((string) $value) > (int) $max) {
            $this->errors[$field] = "El campo {$field} debe tener máximo {$max} caracteres.";
        }
    }

    private function validateRegex(string $field, mixed $value, ?string $regex): void
    {
        if (!empty($value) && !preg_match($regex, (string) $value)) {
            $this->errors[$field] = "El campo {$field} tiene formato inválido.";
        }
    }

    private function validateConfirmed(string $field, mixed $value): void
    {
        $confirmed = $this->data[$field . '_confirmation'] ?? null;
        if ($value !== $confirmed) {
            $this->errors[$field] = "El campo {$field} no coincide con la confirmación.";
        }
    }

    public function fails(): bool
    {
        return !empty($this->errors);
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFirstError(): ?string
    {
        return array_values($this->errors)[0] ?? null;
    }
}
```

### Uso en AuthController:

```php
public function login(): void
{
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // ✅ Validar entrada
    $validator = Validator::make($input, [
        'email' => 'required|email|max:150',
        'password' => 'required|min:6|max:255',
    ]);

    if ($validator->fails()) {
        respondJson([
            'success' => false,
            'message' => $validator->getFirstError(),
            'errors' => $validator->getErrors()
        ], 422);
    }

    // ... rest del código
}
```

---

## 2. LOGGING CON MONOLOG (CRÍTICO)

### Instalar:
```bash
composer require monolog/monolog
```

### Crear: `backend/helpers/Logger.php`

```php
<?php

declare(strict_types=1);

use Monolog\Logger;
use Monolog\Handlers\StreamHandler;
use Monolog\Handlers\RotatingFileHandler;
use Monolog\Formatter\LineFormatter;

class AppLogger
{
    private static ?Logger $instance = null;

    public static function getInstance(): Logger
    {
        if (self::$instance === null) {
            self::$instance = new Logger('EVORIA');

            $logPath = APP_ROOT . '/storage/logs';
            if (!is_dir($logPath)) {
                mkdir($logPath, 0755, true);
            }

            // Handler para archivos
            $handler = new RotatingFileHandler(
                $logPath . '/app.log',
                30, // Máximo 30 archivos
                Logger::DEBUG
            );

            $formatter = new LineFormatter(
                "[%datetime%] %channel%.%level_name%: %message%\n",
                'Y-m-d H:i:s'
            );
            $handler->setFormatter($formatter);
            self::$instance->pushHandler($handler);

            // Handler para errores críticos
            $errorHandler = new StreamHandler(
                $logPath . '/errors.log',
                Logger::ERROR
            );
            self::$instance->pushHandler($errorHandler);
        }

        return self::$instance;
    }

    public static function info(string $message, array $context = []): void
    {
        self::getInstance()->info($message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::getInstance()->error($message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::getInstance()->warning($message, $context);
    }

    public static function debug(string $message, array $context = []): void
    {
        self::getInstance()->debug($message, $context);
    }
}
```

### Uso:

```php
// En AuthService.php
try {
    $user = $this->userRepository->findByEmail($email);
} catch (Exception $e) {
    AppLogger::error('Login attempt failed', [
        'email' => $email,
        'error' => $e->getMessage()
    ]);
    return ['success' => false, 'message' => 'Error de servidor'];
}
```

---

## 3. CONFIGURAR CORS (CRÍTICO)

### Agregar en: `backend/config/bootstrap.php`

```php
// Después de session_start()

// CORS Configuration
if (env('APP_ENV') === 'development') {
    header('Access-Control-Allow-Origin: *');
} else {
    header('Access-Control-Allow-Origin: ' . env('APP_URL', 'http://localhost'));
}

header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-CSRF-TOKEN');
header('Access-Control-Allow-Credentials: true');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}
```

---

## 4. AUTENTICACIÓN JWT (ALTA PRIORIDAD)

### Instalar:
```bash
composer require firebase/php-jwt
```

### Crear: `backend/services/JwtService.php`

```php
<?php

declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $secretKey;
    private string $algorithm = 'HS256';

    public function __construct()
    {
        $this->secretKey = env('JWT_SECRET', 'your-secret-key-change-in-env');
    }

    public function generateToken(array $payload, int $expiresIn = 3600): string
    {
        $now = time();
        $payload['iat'] = $now;
        $payload['exp'] = $now + $expiresIn;

        return JWT::encode($payload, $this->secretKey, $this->algorithm);
    }

    public function validateToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded;
        } catch (Exception $e) {
            AppLogger::warning('Invalid JWT token', ['error' => $e->getMessage()]);
            return null;
        }
    }

    public function refreshToken(array $payload, int $expiresIn = 3600): string
    {
        unset($payload['iat'], $payload['exp']);
        return $this->generateToken($payload, $expiresIn);
    }
}
```

### Actualizar bootstrap.php:

```php
// Agregar función helper
function getBearerToken(): ?string
{
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s+(.+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}
```

---

## 5. MIDDLEWARE DE AUTENTICACIÓN JWT

### Crear: `backend/middleware/JwtAuthMiddleware.php`

```php
<?php

declare(strict_types=1);

class JwtAuthMiddleware
{
    public static function authenticate(): array
    {
        $token = getBearerToken();

        if (!$token) {
            respondJson([
                'success' => false,
                'message' => 'Token no proporcionado.'
            ], 401);
        }

        $jwtService = new JwtService();
        $payload = $jwtService->validateToken($token);

        if (!$payload) {
            respondJson([
                'success' => false,
                'message' => 'Token inválido o expirado.'
            ], 401);
        }

        $_SESSION['user'] = [
            'id' => $payload['user_id'] ?? null,
            'email' => $payload['email'] ?? null,
            'role_name' => $payload['role_name'] ?? null,
        ];

        return $payload;
    }

    public static function authorizeRole(array $allowedRoles): void
    {
        $userRole = $_SESSION['user']['role_name'] ?? '';
        if (!in_array($userRole, $allowedRoles, true)) {
            respondJson([
                'success' => false,
                'message' => 'Permisos insuficientes.'
            ], 403);
        }
    }
}
```

---

## 6. ACTUALIZAR AuthController CON JWT

```php
public function login(): void
{
    $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

    // Validación
    $validator = Validator::make($input, [
        'email' => 'required|email',
        'password' => 'required|min:6',
    ]);

    if ($validator->fails()) {
        respondJson([
            'success' => false,
            'errors' => $validator->getErrors()
        ], 422);
    }

    $result = $this->authService->login($input['email'], $input['password']);

    if (!$result['success']) {
        AppLogger::warning('Login fallido', ['email' => $input['email']]);
        respondJson($result, 401);
    }

    // Generar JWT
    $jwtService = new JwtService();
    $token = $jwtService->generateToken([
        'user_id' => $result['user']['id'],
        'email' => $result['user']['email'],
        'role_name' => $result['user']['role_name'],
    ]);

    AppLogger::info('Login exitoso', ['email' => $input['email']]);

    respondJson([
        'success' => true,
        'message' => 'Login exitoso',
        'token' => $token,
        'user' => $result['user'],
        'expires_in' => 3600,
    ], 200);
}
```

---

## 7. CONTENEDOR DE INYECCIÓN DE DEPENDENCIAS (Básico)

### Crear: `backend/helpers/Container.php`

```php
<?php

declare(strict_types=1);

class Container
{
    private static array $bindings = [];
    private static array $singletons = [];

    public static function bind(string $name, callable $resolver): void
    {
        self::$bindings[$name] = $resolver;
    }

    public static function singleton(string $name, callable $resolver): void
    {
        if (!isset(self::$singletons[$name])) {
            self::$singletons[$name] = $resolver();
        }
    }

    public static function make(string $name): mixed
    {
        if (isset(self::$singletons[$name])) {
            return self::$singletons[$name];
        }

        if (isset(self::$bindings[$name])) {
            return call_user_func(self::$bindings[$name]);
        }

        throw new Exception("No binding found for: {$name}");
    }
}
```

### Uso en `backend/routes/api.php`:

```php
// Registrar bindings
Container::singleton('database', fn() => Database::getConnection());
Container::singleton('userRepository', fn() => new UserRepository());
Container::singleton('authService', fn() => new AuthService(
    Container::make('userRepository')
));
Container::singleton('authController', fn() => new AuthController(
    Container::make('authService')
));

// Usar en rutas
if ($method === 'POST' && $route === 'login') {
    Container::make('authController')->login();
}
```

---

## 8. ENUMERACIÓN DE ESTADOS (PHP 8.1+)

### Crear: `backend/enums/EventStatus.php`

```php
<?php

declare(strict_types=1);

enum EventStatus: string
{
    case PLANNING = 'Planificación';
    case CONFIRMED = 'Confirmado';
    case IN_PROGRESS = 'En ejecución';
    case FINISHED = 'Finalizado';
    case CANCELLED = 'Cancelado';

    public function label(): string
    {
        return match ($this) {
            self::PLANNING => 'En Planificación',
            self::CONFIRMED => 'Confirmado',
            self::IN_PROGRESS => 'En Ejecución',
            self::FINISHED => 'Finalizado',
            self::CANCELLED => 'Cancelado',
        };
    }
}
```

---

## 9. CONFIGURAR .env CORRECTAMENTE

```env
APP_ENV=development
APP_NAME=EVORIA
APP_URL=http://localhost:8000

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=evoria
DB_USER=evoria_user
DB_PASS=evoria_password

SESSION_LIFETIME=3600
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_MINUTES=15

JWT_SECRET=your-very-long-and-secure-random-key-here-minimum-32-chars
JWT_EXPIRES_IN=3600

SESSION_COOKIE_SECURE=0
SESSION_COOKIE_HTTP_ONLY=1
```

---

## 10. EJECUTAR EN ORDEN

1. Crear `Validator.php` y actualizar `AuthController.php`
2. Instalar Monolog y crear `Logger.php`
3. Agregar CORS en `bootstrap.php`
4. Instalar JWT y crear `JwtService.php`
5. Crear `JwtAuthMiddleware.php`
6. Actualizar `AuthController.php` con JWT
7. Crear `Container.php`
8. Refactorizar `api.php` para usar Container
9. Crear enums de estados
10. Actualizar `.env`

---

## COMANDOS DE INSTALACIÓN

```bash
# Instalar dependencias
composer require monolog/monolog firebase/php-jwt

# Crear carpetas necesarias
mkdir -p storage/logs
chmod 755 storage/logs

# (Opcional) Actualizar composer.json con autoload
# Agregar en "autoload": { "psr-4": { "App\\": "backend/" }}
```

---

*Guía de implementación técnica - EVORIA v1.0*
