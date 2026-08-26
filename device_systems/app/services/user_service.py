from typing import Any

from fastapi import HTTPException, status

from app.data.users_db import next_user_id, users_db
from app.schemas.user_schema import UserCreate, UserPatch, UserUpdate


def _ensure_email_available(email: str, current_user_id: int | None = None) -> None:
    if any(user["email"] == email and user["id"] != current_user_id for user in users_db):
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="El correo electrónico ya está registrado")


def list_users(role: str | None = None, is_active: bool | None = None) -> list[dict[str, Any]]:
    return [user for user in users_db if (role is None or user["role"] == role) and (is_active is None or user["is_active"] == is_active)]


def create_user(user_data: UserCreate) -> dict[str, Any]:
    _ensure_email_available(str(user_data.email))
    user = {"id": next_user_id(), **user_data.model_dump(mode="json")}
    users_db.append(user)
    return user


def replace_user(user: dict[str, Any], user_data: UserUpdate) -> dict[str, Any]:
    _ensure_email_available(str(user_data.email), user["id"])
    user.update(user_data.model_dump(mode="json"))
    return user


def update_user(user: dict[str, Any], user_data: UserPatch) -> dict[str, Any]:
    changes = user_data.model_dump(exclude_unset=True, mode="json")
    if not changes:
        raise HTTPException(status_code=status.HTTP_400_BAD_REQUEST, detail="Debe enviar al menos un campo para actualizar")
    if "email" in changes:
        _ensure_email_available(changes["email"], user["id"])
    user.update(changes)
    return user


def delete_user(user: dict[str, Any]) -> None:
    users_db.remove(user)