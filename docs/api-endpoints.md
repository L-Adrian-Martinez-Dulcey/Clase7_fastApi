# EVORIA API

## Auth

### POST /api/login
Body JSON:
```json
{
  "email": "admin@evoria.com",
  "password": "admin123"
}
```

### POST /api/logout
Requiere sesión activa.

### GET /api/me
Devuelve el usuario autenticado.

## Estado actual
La API base de autenticación ya está creada en backend/routes/api.php y el frontend de login y dashboard ya existe. Los módulos avanzados de eventos, recursos e reportes siguen pendientes de implementación completa.
