# EVORIA – Sistema Inteligente de Logística y Planificación de Eventos

## Descripción
EVORIA es una plataforma web para empresas organizadoras de eventos que necesitan centralizar la planificación, logística y administración de sus proyectos.

Eslogan: “Donde la planificación se convierte en resultados.”

## Requisitos
- PHP 8.2+
- MySQL 8
- Apache o servidor web compatible
- Git
- Navegador web moderno

## Estructura del proyecto
```
EVORIA/
├── backend/
│   ├── config/
│   ├── controllers/
│   ├── middleware/
│   ├── models/
│   ├── repositories/
│   ├── routes/
│   └── services/
├── database/
│   ├── migrations/
│   ├── seeds/
│   └── scripts/
├── docs/
├── frontend/
│   ├── assets/
│   ├── css/
│   ├── js/
│   └── views/
├── .env.example
├── index.php
├── README.md
└── .gitignore
```

## Instalación
1. Clonar el repositorio.
2. Crear una copia de `.env.example` como `.env`.
3. Configurar conexión con MySQL.
4. Crear la base de datos `evoria`.
5. Ejecutar migraciones y seeders.
6. Servir la aplicación desde la raíz del proyecto con Apache.

## Variables de entorno
```env
APP_ENV=development
APP_NAME=EVORIA
APP_URL=http://localhost
DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=evoria
DB_USER=evoria_user
DB_PASS=evoria_password
SESSION_LIFETIME=3600
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_MINUTES=15
```

## Base de datos
Ejecuta lo siguiente en MySQL:
```sql
CREATE DATABASE evoria;
CREATE USER 'evoria_user'@'localhost' IDENTIFIED BY 'evoria_password';
GRANT ALL PRIVILEGES ON evoria.* TO 'evoria_user'@'localhost';
FLUSH PRIVILEGES;
```

## Usuarios de prueba
- Administrador: admin@evoria.com / admin123
- Coordinador: coord@evoria.com / coord123
- Empleado: empleado@evoria.com / empleado123
- Proveedor: proveedor@evoria.com / proveedor123

## Ejecución
- Inicia Apache.
- Accede a `http://localhost/EVORIA/index.php`.
- En login prueba los usuarios anteriores.

## API
- `POST /api/login`
- `POST /api/logout`
- `GET /api/me`

## Estado actual
Este proyecto se encuentra en una primera fase funcional de base y autenticación. La estructura principal, la API, la interfaz y la lógica base ya están creadas. Los módulos avanzados como eventos, cronogramas, presupuestos, inventario, alertas, reportes y auditoría están pendientes de implementación real con base de datos completa.

## Siguiente paso recomendado
Continuar con:
1. Migraciones de MySQL.
2. CRUD de eventos.
3. CRUD de recursos e inventario.
4. Asignación de recursos con validación.
5. Cronogramas y presupuestos.
6. Alertas, reportes y auditoría.
7. Pruebas y seguridad avanzada.
