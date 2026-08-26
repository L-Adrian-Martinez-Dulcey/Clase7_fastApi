# 🔒 CHECKLIST DE SEGURIDAD - EVORIA

## AUDITORÍA DE SEGURIDAD

---

## ✅ ELEMENTOS CORRECTOS

- [x] **Hashing de contraseñas:** Usa `password_verify()` ✓
- [x] **Protección contra Brute Force:** Sistema de bloqueo de cuenta ✓
- [x] **Prepared Statements:** PDO evita SQL Injection ✓
- [x] **CSRF Token:** Generación de tokens (aunque sin validación completa) ⚠️
- [x] **HttpOnly Cookies:** `session.cookie_httponly = 1` ✓
- [x] **SameSite:** `session.cookie_samesite = Strict` ✓
- [x] **Validación de autorización:** Middleware de roles implementado ✓

---

## ⚠️ PROBLEMAS DE SEGURIDAD

### 1. CRÍTICO: Session Cookie Secure Flag

**Problema:**
```php
ini_set('session.cookie_secure', '0');  // ❌ INSEGURO EN PRODUCCIÓN
```

**Impacto:** Las cookies se transmiten por HTTP sin encriptación  
**Solución:**
```php
// backend/config/bootstrap.php
ini_set('session.cookie_secure', env('SESSION_COOKIE_SECURE', '1') === '1' ? '1' : '0');
```

**.env:**
```env
SESSION_COOKIE_SECURE=1
```

---

### 2. CRÍTICO: Display Errors en Producción

**Problema:**
```php
ini_set('display_errors', '1');  // ❌ Expone paths y detalles internos
```

**Impacto:** Información sensible visible a atacantes  
**Solución:**
```php
if (env('APP_ENV') === 'production') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(0);
} else {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
}
```

---

### 3. CRÍTICO: Validación de Email Ausente

**Problema:**
```php
$email = trim((string) ($input['email'] ?? ''));
if ($email === '') { /* error */ }
// ❌ No valida formato de email
```

**Solución:**
```php
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    respondJson(['success' => false, 'message' => 'Email inválido'], 422);
}
```

---

### 4. CRÍTICO: CSRF Validation Incompleta

**Problema:**
```php
// ❌ Valida BUT ONLY IF header presente
if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    if ($_SERVER['HTTP_X_CSRF_TOKEN'] !== $_SESSION['csrf_token']) { /* error */ }
}
```

**Solución:**
```php
public static function csrfCheck(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? $_POST['_token'] ?? '';
        
        if (empty($_SESSION['csrf_token']) || $token !== $_SESSION['csrf_token']) {
            respondJson(['success' => false, 'message' => 'CSRF token inválido'], 419);
        }
    }
}
```

---

### 5. ALTA: Inyección de Dependencias Directa

**Problema:**
```php
// ❌ Dependencias instanciadas directamente
new AuthService(new UserRepository());
```

**Riesgo:** Difícil testear, difícil cambiar implementaciones  
**Solución:** Usar contenedor de DI (ver guía técnica)

---

### 6. ALTA: Rate Limiting Ausente

**Problema:** No hay límite de intentos a endpoints (solo a login)  
**Solución:**
```php
class RateLimiter
{
    public static function checkLimit(string $identifier, int $limit = 60, int $window = 60): bool
    {
        $key = "rate_limit:{$identifier}";
        $attempts = apcu_fetch($key) ?: 0;
        
        if ($attempts >= $limit) {
            return false;
        }
        
        apcu_store($key, $attempts + 1, $window);
        return true;
    }
}

// En api.php
if (!RateLimiter::checkLimit($_SERVER['REMOTE_ADDR'])) {
    respondJson(['success' => false, 'message' => 'Demasiadas solicitudes'], 429);
}
```

---

### 7. ALTA: Falta Validación de Autorización

**Problema:**
```php
// ❌ Cualquier usuario autenticado puede acceder
if ($method === 'GET' && $route === 'eventos') {
    $eventController->list();
}
```

**Solución:**
```php
if ($method === 'GET' && $route === 'eventos') {
    requireAuth();  // Validar que esté autenticado
    $eventController->list();
}
```

---

### 8. ALTA: No hay Sanitización de Salida

**Problema:**
```php
// ❌ Sin escapar
respondJson(['name' => $_POST['name']]);
```

**Solución:**
```php
respondJson(['name' => htmlspecialchars($_POST['name'], ENT_QUOTES, 'UTF-8')]);
```

---

### 9. MEDIA: Exposición de Stack Trace

**Problema:**
```php
// ❌ En catch, podrías exponer stack trace
} catch (Exception $e) {
    respondJson(['error' => $e->getMessage()], 500);
}
```

**Solución:**
```php
} catch (Exception $e) {
    AppLogger::error($e->getMessage(), ['trace' => $e->getTraceAsString()]);
    respondJson(['success' => false, 'message' => 'Error interno del servidor'], 500);
}
```

---

### 10. MEDIA: Falta .env en .gitignore

**Problema:** `.env` podría subirse a Git con credenciales  
**Solución:**
```bash
# .gitignore
.env
.env.local
storage/logs/*
vendor/
node_modules/
```

---

### 11. MEDIA: Control de Acceso en Repositorio

**Problema:**
```php
// ❌ Sin verificar permisos en repository
public function findByEmail(string $email): ?array
{
    // Retorna cualquier usuario
}
```

**Solución:** Agregar validación en service/controller

---

### 12. BAJA: Headers de Seguridad Faltantes

**Problema:** No hay headers de seguridad HTTP  
**Solución:**
```php
// En bootstrap.php
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
header('Content-Security-Policy: default-src \'self\'');
```

---

## 🚀 PLAN DE CORRECCIÓN POR PRIORIDAD

### INMEDIATO (Hoy)
1. Cambiar `session.cookie_secure` a `'1'`
2. Ocultar `display_errors` en producción
3. Agregar validación de email con `filter_var`
4. Agregar validación de CSRF en todos los POST/PUT/DELETE

### HOY + 1
5. Implementar Rate Limiting
6. Agregar middleware de autorización
7. Implementar sanitización en salida JSON

### HOY + 2
8. Agregar headers de seguridad HTTP
9. Implementar contenedor DI
10. Mejorar manejo de excepciones con logging

### HOY + 3
11. Crear tests de seguridad
12. Implementar auditoría de acciones
13. Documentar políticas de seguridad

---

## 🔐 CONFIGURACIÓN RECOMENDADA PARA PRODUCCIÓN

```php
// backend/config/bootstrap.php - PRODUCCIÓN

if (env('APP_ENV') === 'production') {
    // Seguridad de sesión
    ini_set('session.cookie_secure', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    
    // Errores
    error_reporting(0);
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    ini_set('error_log', APP_ROOT . '/storage/logs/php-errors.log');
    
    // Performance & Security
    ini_set('expose_php', '0');
    ini_set('memory_limit', '256M');
    ini_set('post_max_size', '50M');
    ini_set('upload_max_filesize', '50M');
    
    // Limpieza automática de sesiones
    ini_set('session.gc_maxlifetime', '3600');
    ini_set('session.gc_probability', '1');
    ini_set('session.gc_divisor', '100');
}
```

### .env PRODUCCIÓN

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://evoria.example.com

DB_HOST=db-prod.internal
DB_PORT=3306
DB_NAME=evoria_prod
DB_USER=evoria_user_prod
DB_PASS=***SECURE_PASSWORD_HERE***

SESSION_LIFETIME=1800
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_MINUTES=30

JWT_SECRET=***VERY_LONG_RANDOM_SECRET_KEY_64_CHARS_MINIMUM***
JWT_EXPIRES_IN=1800

SESSION_COOKIE_SECURE=1
SESSION_COOKIE_HTTP_ONLY=1
```

---

## ✨ TEST DE SEGURIDAD MANUAL

### 1. Probar SQL Injection
```javascript
// En login, probar con:
email: "admin' OR '1'='1"
```
**Debe fallar** (prepared statement protege)

### 2. Probar XSS
```javascript
// En crear evento, probar:
nombre: "<script>alert('XSS')</script>"
```
**Debe sanitizar** (verificar que aparezca como texto)

### 3. Probar CSRF
```javascript
// Cambiar token y enviar POST
X-CSRF-TOKEN: "token_falso"
```
**Debe retornar 419** (CSRF validation error)

### 4. Probar Brute Force
```bash
# Intentar login 6 veces con contraseña incorrecta
for i in {1..6}; do
  curl -X POST http://localhost/api/login \
    -d '{"email":"admin@evoria.com","password":"wrong"}' \
    -H "Content-Type: application/json"
done
```
**Debe bloquear cuenta en intento 6**

### 5. Probar Path Traversal
```javascript
// En recurso, probar:
url: "../../etc/passwd"
```
**Debe rechazar**

---

## 📊 TABLA DE RIESGOS

| # | Riesgo | Severidad | Estado | Deadline |
|---|--------|-----------|--------|----------|
| 1 | Session Cookie Secure | CRÍTICO | ⏳ Por hacer | Hoy |
| 2 | Display Errors | CRÍTICO | ⏳ Por hacer | Hoy |
| 3 | Validación Email | CRÍTICO | ⏳ Por hacer | Hoy |
| 4 | CSRF Incompleto | CRÍTICO | ⚠️ Parcial | Hoy |
| 5 | Rate Limiting | ALTA | ⏳ Por hacer | Mañana |
| 6 | Autorización | ALTA | ⚠️ Parcial | Mañana |
| 7 | Sanitización | ALTA | ⏳ Por hacer | Mañana |
| 8 | Stack Trace | MEDIA | ⏳ Por hacer | 2 días |
| 9 | .gitignore | MEDIA | ✅ OK | - |
| 10 | Headers HTTP | MEDIA | ⏳ Por hacer | 2 días |
| 11 | Logging | MEDIA | ⏳ Por hacer | 2 días |
| 12 | Testing | MEDIA | ⏳ Por hacer | 3 días |

---

## 🎯 CONCLUSIÓN

**Seguridad General: 6/10**

**Lo bueno:**
- Hashing de contraseñas funciona
- Prepared statements protegen contra SQL Injection
- Sistema de bloqueo de cuenta contra Brute Force
- CSRF token generado

**Lo malo:**
- Session cookie no segura en desarrollo/producción
- Errores expuestos en pantalla
- Validación incompleta
- No hay rate limiting global
- Falta sanitización de salida

**Acción inmediata:** Implementar los cambios críticos en bootstrap.php y AuthController.php

---

*Auditoría de seguridad completada - 13/08/2026*
