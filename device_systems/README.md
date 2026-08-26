# device_systems API

## 1. Que es esta API

Esta es la actividad de FastAPI integrada dentro del proyecto EVORIA. Su objetivo es practicar como evoluciona una API sencilla de usuarios hasta convertirse en un CRUD completo.

El recurso principal es `users` y se puede:

- listar usuarios;
- consultar un usuario por ID;
- crear usuarios;
- actualizar completamente un usuario con `PUT`;
- actualizar parcialmente un usuario con `PATCH`;
- eliminar usuarios con `DELETE`.

La API se ejecuta como un servicio FastAPI separado del backend PHP de EVORIA. Esto permite conservar funcionando el sistema web actual y, al mismo tiempo, demostrar la actividad con la tecnologia solicitada.

## 2. Como iniciar el servidor

Desde una terminal ubicada en la carpeta `Evoria`:

```powershell
cd device_systems
py -m pip install -r requirements.txt
py -m uvicorn app.main:app --reload
```

El servidor queda disponible en:

- API: `http://127.0.0.1:8000`
- Swagger UI: `http://127.0.0.1:8000/docs`
- ReDoc: `http://127.0.0.1:8000/redoc`

Para detenerlo, presiona `Ctrl+C` en la terminal.

## 3. Que aparece en Swagger

Abre `/docs`. Veras dos grupos:

### Health

Contiene `GET /`, que confirma que la API esta activa.

Respuesta:

```json
{
  "message": "EVORIA device_systems API activa",
  "version": "2.0.0"
}
```

### Users

Contiene todas las operaciones del CRUD. Cada endpoint tiene una descripcion, un resumen, el modelo esperado y los posibles codigos de respuesta.

## 4. Flujo de una peticion

Cuando llega una peticion, FastAPI sigue este flujo:

1. `app/main.py` crea la aplicacion, define los metadatos y registra las rutas.
2. `app/routes/user_routes.py` identifica el metodo y la URL.
3. Pydantic valida el cuerpo, el correo, el nombre y el rol.
4. `Depends()` ejecuta `get_user_or_404` cuando la ruta necesita un usuario existente.
5. `app/services/user_service.py` aplica las reglas de negocio.
6. `app/data/users_db.py` consulta o modifica la lista de usuarios en memoria.
7. FastAPI convierte el resultado al esquema de respuesta y devuelve el codigo HTTP correspondiente.

La ruta no contiene toda la logica porque cada carpeta tiene una responsabilidad concreta.

## 5. Estructura del codigo

```text
device_systems/
├── app/
│   ├── data/users_db.py
│   ├── dependencies/user_dependencies.py
│   ├── routes/user_routes.py
│   ├── schemas/user_schema.py
│   ├── services/user_service.py
│   └── main.py
├── tests/test_users.py
├── requirements.txt
└── README.md
```

### `app/main.py`

Crea la instancia `FastAPI`, configura titulo, descripcion, version y contacto, y registra el router de usuarios.

### `app/routes/user_routes.py`

Define las URLs y los metodos HTTP. Las rutas reciben datos validados y llaman al servicio.

### `app/schemas/user_schema.py`

Define los modelos Pydantic:

- `UserCreate`: campos necesarios para crear.
- `UserUpdate`: todos los campos necesarios para un `PUT`.
- `UserPatch`: campos opcionales para un `PATCH`.
- `UserResponse`: formato que devuelve la API.

El rol solo puede ser `admin`, `support` o `user`. Un correo invalido genera `422` automaticamente.

### `app/services/user_service.py`

Contiene las reglas de negocio: evitar correos duplicados, crear IDs, actualizar usuarios y eliminar usuarios.

### `app/dependencies/user_dependencies.py`

Contiene la dependencia reutilizable `get_user_or_404`. Busca el ID recibido en la URL y, si no existe, detiene la peticion con:

```json
{
  "detail": "Usuario no encontrado"
}
```

Esto evita repetir la misma busqueda en GET, PUT, PATCH y DELETE.

### `app/data/users_db.py`

Simula una base de datos con una lista de diccionarios. Hay dos usuarios iniciales:

```json
[
  {
    "id": 1,
    "name": "Ana Torres",
    "email": "ana@example.com",
    "role": "admin",
    "is_active": true
  },
  {
    "id": 2,
    "name": "Luis Perez",
    "email": "luis@example.com",
    "role": "support",
    "is_active": true
  }
]
```

Al reiniciar el servidor, los datos vuelven a este estado. Esto es intencional: la actividad solicita una base de datos simulada en memoria.

## 6. Operaciones y ejemplos

### Listar usuarios

`GET /users`

Tambien se puede filtrar:

- `GET /users?role=support`
- `GET /users?is_active=true`
- `GET /users?role=admin&is_active=true`

Respuesta `200`:

```json
[
  {
    "id": 1,
    "name": "Ana Torres",
    "email": "ana@example.com",
    "role": "admin",
    "is_active": true
  }
]
```

### Consultar por ID

`GET /users/1`

Si existe, responde `200`. Si no existe, por ejemplo `GET /users/999`, responde `404`.

### Crear usuario

`POST /users`

Body:

```json
{
  "name": "Marta Diaz",
  "email": "marta@example.com",
  "role": "user",
  "is_active": true
}
```

Responde `201 Created` y agrega un ID nuevo.

### Actualizar completamente con PUT

`PUT /users/1`

`PUT` reemplaza todos los campos editables, por eso deben enviarse todos:

```json
{
  "name": "Ana Torres Actualizada",
  "email": "ana.nueva@example.com",
  "role": "support",
  "is_active": false
}
```

Responde `200 OK`. Si falta un campo obligatorio, FastAPI responde `422`.

### Actualizar parcialmente con PATCH

`PATCH /users/1`

Solo se envia lo que se quiere cambiar:

```json
{
  "role": "admin"
}
```

Responde `200 OK`. Un body vacio no cambia nada y responde `400`:

```json
{
  "detail": "Debe enviar al menos un campo para actualizar"
}
```

### Eliminar usuario

`DELETE /users/1`

Si existe, elimina el usuario y responde `204 No Content`, por eso no aparece un body. Si el ID no existe, responde `404`.

## 7. Codigos de respuesta

| Codigo | Significado |
|---|---|
| `200` | Consulta o actualizacion exitosa |
| `201` | Usuario creado |
| `204` | Usuario eliminado sin body |
| `400` | Correo duplicado o PATCH vacio |
| `404` | Usuario no encontrado |
| `422` | Body invalido, correo invalido o rol no permitido |

## 8. Pruebas

Ejecuta desde `Evoria/device_systems`:

```powershell
py -m pytest -q
```

Las pruebas verifican el listado, filtros, creacion, PUT, PATCH, DELETE y los errores principales de la actividad.

## 9. Por que esta implementacion esta dentro de EVORIA

EVORIA ya tiene un backend PHP con sesiones y MySQL. La actividad exige FastAPI, Pydantic v2, `Depends()` y una estructura diferente. Por eso la API esta en `Evoria/device_systems`: pertenece al mismo repositorio y queda documentada junto al proyecto, pero se ejecuta con Uvicorn como servicio independiente para no romper el backend PHP existente.

El frontend PHP se inicia como antes. La API educativa se inicia con Uvicorn en el puerto `8000`.