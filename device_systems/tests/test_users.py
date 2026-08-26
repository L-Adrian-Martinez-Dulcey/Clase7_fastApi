from fastapi.testclient import TestClient

from app.data.users_db import users_db
from app.main import app

client = TestClient(app)


def setup_function():
    users_db[:] = [
        {"id": 1, "name": "Ana Torres", "email": "ana@example.com", "role": "admin", "is_active": True},
        {"id": 2, "name": "Luis Perez", "email": "luis@example.com", "role": "support", "is_active": True},
    ]


def test_crud_and_filters():
    assert client.get("/users").status_code == 200
    assert len(client.get("/users?role=support").json()) == 1
    created = client.post("/users", json={"name": "Marta Diaz", "email": "marta@example.com", "role": "user", "is_active": True})
    assert created.status_code == 201
    assert client.put("/users/3", json={"name": "Marta D.", "email": "marta2@example.com", "role": "support", "is_active": False}).status_code == 200
    assert client.patch("/users/3", json={"role": "admin"}).status_code == 200
    assert client.delete("/users/3").status_code == 204


def test_errors():
    assert client.get("/users/999").status_code == 404
    assert client.post("/users", json={"name": "Otra Persona", "email": "ana@example.com", "role": "user", "is_active": True}).status_code == 400
    assert client.patch("/users/1", json={}).status_code == 400
    assert client.post("/users", json={"name": "Invalido", "email": "no-es-correo", "role": "user", "is_active": True}).status_code == 422
    assert client.delete("/users/999").status_code == 404