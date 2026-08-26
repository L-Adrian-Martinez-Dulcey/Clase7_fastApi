from fastapi import FastAPI

from app.routes.user_routes import router as user_router

app = FastAPI(
    title="EVORIA device_systems API",
    description="API REST para la gestión de usuarios del sistema EVORIA.",
    version="2.0.0",
    contact={"name": "Equipo EVORIA", "email": "soporte@evoria.example.com"},
)
app.include_router(user_router)


@app.get("/", tags=["Health"], summary="Comprobar disponibilidad")
def health_check():
    return {"message": "EVORIA device_systems API activa", "version": app.version}