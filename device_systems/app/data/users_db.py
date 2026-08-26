from typing import Any

users_db: list[dict[str, Any]] = [
    {"id": 1, "name": "Ana Torres", "email": "ana@example.com", "role": "admin", "is_active": True},
    {"id": 2, "name": "Luis Perez", "email": "luis@example.com", "role": "support", "is_active": True},
]


def next_user_id() -> int:
    return max((user["id"] for user in users_db), default=0) + 1