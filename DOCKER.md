# Biblioteca Digital con Docker

Este proyecto puede ejecutarse de dos formas:

- **XAMPP**, usando Apache/MySQL de tu computadora.
- **Docker**, usando contenedores propios para PHP/Apache, MariaDB y phpMyAdmin.

Docker no es obligatorio. El mismo código funciona en ambos modos gracias a `.env` y `.env.docker`.

---

## Servicios incluidos

| Servicio | Contenedor | URL / Puerto |
|---|---|---|
| PHP + Apache | `biblioteca_app` | http://localhost:8080 |
| MariaDB | `biblioteca_db` | localhost:3307 desde tu PC |
| phpMyAdmin | `biblioteca_phpmyadmin` | http://localhost:8081 |

---

## Ejecutar con Docker

Desde la raíz del proyecto:

```bash
docker compose up -d --build
```

Luego abre:

```txt
http://localhost:8080
```

phpMyAdmin:

```txt
http://localhost:8081
```

---

## Credenciales de prueba

### Administrador

```txt
usuario: admin
contraseña: root2514
```

### Estudiante

```txt
email: user@correo.com
contraseña: User123*
```

### Docente

```txt
email: docente@correo.com
contraseña: Docente1*
```

### Base de datos

```txt
host dentro de Docker: db
host desde tu PC: 127.0.0.1
puerto desde tu PC: 3307
base: biblioteca_digital
usuario app: bib_app
contraseña: B!bl10t3c@_S3cur3#2025
root: root2514
```

---

## Apagar Docker

```bash
docker compose down
```

---

## Reiniciar desde cero la base de datos

Esto borra el volumen de MariaDB y vuelve a importar el SQL inicial:

```bash
docker compose down -v
docker compose up -d --build
```

---

## Archivos importantes

```txt
Dockerfile
    Construye PHP 8.2 + Apache + PDO MySQL + GD.

docker-compose.yml
    Levanta app, db y phpMyAdmin.

.env
    Configuración local para XAMPP.

.env.docker
    Configuración usada por Docker.

docker/mysql/init/01_biblioteca_digital.sql
    Script que se importa automáticamente al crear el contenedor de MariaDB.

conexion/biblioteca_digital.sql
    El mismo script SQL completo para importarlo manualmente en phpMyAdmin si usas XAMPP.
```

---

## Cambiar puertos

Edita `.env` si quieres cambiar los puertos usados por Docker:

```env
APP_PORT=8080
DB_PORT_PUBLIC=3307
PHPMYADMIN_PORT=8081
```

Ejemplo:

```env
APP_PORT=8090
DB_PORT_PUBLIC=3310
PHPMYADMIN_PORT=8091
```

Después ejecuta:

```bash
docker compose up -d --build
```

---

## Nota importante

Dentro de Docker el PHP no debe conectarse a `localhost` para la base de datos. Debe conectarse a:

```env
DB_HOST=db
```

Por eso existe `.env.docker`. En XAMPP se usa normalmente:

```env
DB_HOST=localhost
```
