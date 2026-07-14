# Despliegue en servidor público

Esta versión está preparada para producción usando Docker, Apache/PHP y MariaDB.

## 1. Preparar `.env`

En el servidor:

```bash
cp .env.production.example .env
nano .env
```

Cambia obligatoriamente:

- `APP_URL=https://tu-subdominio.tudominio.com`
- `DB_PASS`
- `DB_ROOT_PASSWORD`
- `APP_SECRET`

Genera secretos largos, por ejemplo:

```bash
openssl rand -base64 48
```

## 2. Levantar contenedores

```bash
docker compose up -d --build
```

La aplicación queda escuchando solo en la máquina local:

```txt
127.0.0.1:8080
```

MariaDB no expone puerto público.

## 3. Configurar subdominio

En el proxy inverso del servidor usa:

```txt
subdominio: biblioteca.tudominio.com
proxy pass: http://127.0.0.1:8080
HTTPS: activo
```

Puedes usar Nginx, Caddy, Traefik, Nginx Proxy Manager o Cloudflare Tunnel.

## 4. No publicar estos servicios

En producción no se publica:

- phpMyAdmin
- Puerto de MariaDB 3306/3307
- Archivos `.env`
- Archivos SQL
- Código fuente fuera de `public/`

## 5. Desarrollo local con phpMyAdmin

Solo para desarrollo:

```bash
docker compose -f docker-compose.yml -f docker-compose.dev.yml up -d --build
```

Luego:

```txt
App:        http://localhost:8080
phpMyAdmin: http://localhost:8081
MariaDB:    localhost:3307
```

## 6. Reiniciar base desde cero

Esto borra los datos:

```bash
docker compose down -v
docker compose up -d --build
```

## 7. Credenciales iniciales de la aplicación

```txt
Admin:
usuario: admin
contraseña: root2514

Estudiante:
email: user@correo.com
contraseña: User123*

Docente:
email: docente@correo.com
contraseña: Docente1*
```

Cambia esas contraseñas después del primer ingreso si el sistema queda público.
