from typing import Annotated, Any

from fastapi import Depends, HTTPException, Path, status

from app.data.users_db import users_db


def get_user_or_404(user_id: Annotated[int, Path(gt=0)]) -> dict[str, Any]:
    user = next((item for item in users_db if item["id"] == user_id), None)
    if user is None:
        raise HTTPException(status_code=status.HTTP_404_NOT_FOUND, detail="Usuario no encontrado")
    return user


CurrentUser = Annotated[dict[str, Any], Depends(get_user_or_404)]