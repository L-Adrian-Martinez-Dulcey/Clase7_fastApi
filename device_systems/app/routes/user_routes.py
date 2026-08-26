from typing import Annotated

from fastapi import APIRouter, Query, Response, status

from app.dependencies.user_dependencies import CurrentUser
from app.schemas.user_schema import Role, UserCreate, UserPatch, UserResponse, UserUpdate
from app.services import user_service

router = APIRouter(prefix="/users", tags=["Users"])


@router.get("", response_model=list[UserResponse], summary="Listar usuarios", description="Lista usuarios y permite filtrar por rol o estado.", response_description="Usuarios encontrados")
def list_users(role: Role | None = Query(default=None), is_active: bool | None = Query(default=None)):
    return user_service.list_users(role, is_active)


@router.get("/{user_id}", response_model=UserResponse, summary="Consultar usuario", description="Obtiene un usuario por su identificador.", response_description="Usuario encontrado")
def get_user(user: CurrentUser):
    return user


@router.post("", response_model=UserResponse, status_code=status.HTTP_201_CREATED, summary="Crear usuario", description="Crea un usuario validando correo y rol.", response_description="Usuario creado")
def create_user(user_data: UserCreate):
    return user_service.create_user(user_data)


@router.put("/{user_id}", response_model=UserResponse, summary="Actualizar usuario completamente", description="Reemplaza todos los campos editables del usuario.", response_description="Usuario actualizado")
def replace_user(user_data: UserUpdate, user: CurrentUser):
    return user_service.replace_user(user, user_data)


@router.patch("/{user_id}", response_model=UserResponse, summary="Actualizar usuario parcialmente", description="Actualiza solo los campos enviados.", response_description="Usuario actualizado")
def update_user(user_data: UserPatch, user: CurrentUser):
    return user_service.update_user(user, user_data)


@router.delete("/{user_id}", status_code=status.HTTP_204_NO_CONTENT, summary="Eliminar usuario", description="Elimina un usuario existente.", response_description="Usuario eliminado")
def delete_user(user: CurrentUser):
    user_service.delete_user(user)
    return Response(status_code=status.HTTP_204_NO_CONTENT)