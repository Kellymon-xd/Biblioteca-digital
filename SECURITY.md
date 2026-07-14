# Medidas de seguridad aplicadas

- DocumentRoot apunta a `public/`, no a la raíz del proyecto.
- `.env`, SQL, Dockerfile, compose y código fuente quedan fuera del acceso público.
- `APP_DEBUG=false` para producción.
- Cookies de sesión con `HttpOnly`, `SameSite=Lax` y `Secure` en producción.
- Cabeceras de seguridad: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, `Permissions-Policy`, `Content-Security-Policy` y HSTS cuando hay HTTPS.
- MariaDB no se expone al exterior en `docker-compose.yml` de producción.
- phpMyAdmin no se incluye en producción; solo está en `docker-compose.dev.yml`.
- API interna desactivada por defecto con `API_ENABLED=false`.
- Apache con `Options -Indexes`, `ServerTokens Prod`, `ServerSignature Off` y `TraceEnable Off`.
- Persistencia separada para base de datos y uploads con volúmenes Docker.

## Pendientes recomendados antes de exponerlo

- Cambiar las contraseñas iniciales de admin, estudiante y docente.
- Usar HTTPS real en el subdominio.
- Mantener el servidor actualizado.
- Hacer respaldos del volumen de MariaDB y de `biblioteca_uploads`.
- No subir `.env` a GitHub.
