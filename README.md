# Biblioteca Digital

Proyecto PHP MVC para gestión de biblioteca digital con MySQL/MariaDB, PDO, roles, reservas, reportes y portal público.

## Requisitos

- PHP 8.1 o superior
- MySQL/MariaDB
- Apache local con XAMPP o Laragon
- Extensiones PHP: PDO MySQL, GD, mbstring

## Instalación rápida

1. Copia el proyecto dentro del directorio público de Apache, por ejemplo `htdocs/Biblioteca digital`.
2. En phpMyAdmin entra con un usuario administrador, normalmente `root`.
3. Importa `conexion/biblioteca_digital.sql` para una instalación limpia.
4. Abre el sistema en `http://localhost/Biblioteca%20digital/` o en la ruta donde lo hayas colocado.

## Configuración con `.env`

El proyecto ya incluye:

- `.env`: configuración local lista para XAMPP.
- `.env.example`: plantilla para otros equipos o para Docker en el futuro.
- `utilidades/Env.php`: cargador de variables sin Composer.

El archivo `.env` no es obligatorio para que el sistema exista, pero si está presente el proyecto lo usa automáticamente.
Si `.env` no existe, se usan valores por defecto en el código.

Variables principales:

```env
APP_URL=
DB_HOST=localhost
DB_PORT=3306
DB_NAME=biblioteca_digital
DB_USER=bib_app
DB_PASS="B!bl10t3c@_S3cur3#2025"
APP_SECRET="biblioteca-digital-cambiar-en-produccion-2026"
POR_PAGINA=10
```

Para XAMPP se puede dejar `APP_URL` vacío, porque el sistema detecta automáticamente la carpeta del proyecto.
Para Docker en el futuro, puedes cambiarlo así:

```env
APP_URL=http://localhost:8080
DB_HOST=db
DB_PORT=3306
```

## Credenciales de prueba

| Tipo | Usuario | Contraseña | Acceso |
| --- | --- | --- | --- |
| Administrador | `admin` | `root2514` | Panel administrativo |
| Estudiante | `user@correo.com` o `8-888-888` | `User123*` | Portal público |
| Docente | `docente@correo.com` o `9-999-999` | `Docente1*` | Portal público |

## Módulos implementados

- Login administrativo con CSRF, bitácora de IP, fecha, navegador y control de errores.
- Bloqueo automático al tercer intento fallido.
- CRUD de usuarios.
- CRUD de roles y permisos por módulo. Un rol puede tener control total o solo algunos módulos.
- CRUD de estudiantes con CIP único.
- CRUD de profesores/docentes con CIP único.
- CRUD de carreras y categorías.
- CRUD de libros con categoría, costo, descripción, imagen original, thumbnail y rutas en BD.
- Reporte de libros exportable a CSV compatible con Excel, filtrado por búsqueda, categoría y disponibilidad.
- Reservas para estudiantes y docentes.
- Cantidad dinámica de días de préstamo para estudiantes, docentes y administrativos.
- Reporte administrativo de reservas filtrado por fechas, con días reservados y tipo de lector, exportable a CSV compatible con Excel.
- Solicitudes de libros no existentes o no disponibles, con materia, motivo e interbibliotecario.
- Estadísticas de libros más usados por período.
- Página pública con stack del sistema, contáctenos e importancia de bibliotecas digitales.
- Conexión centralizada mediante clase `Conexion` y PDO.
- Sanitización, validación, CSRF, HMAC y auditoría de firmas.
- Contratos por interfaz para servicios de transformación de datos: hashing de contraseñas y firma digital.

## Archivos clave

- Configuración general: `config.php`
- Variables de entorno: `.env`, `.env.example`, `utilidades/Env.php`
- Conexión PDO: `conexion/Conexion.php`
- Script SQL completo: `conexion/biblioteca_digital.sql`
- CSRF: `utilidades/CsrfToken.php`
- Validación: `utilidades/Validador.php`
- Sanitización: `utilidades/Sanitizador.php`
- Contrato de transformación: `utilidades/TransformadorDatosInterface.php`
- Hashing de contraseñas: `utilidades/HashPasswordService.php`
- Firma digital: `utilidades/FirmaDigital.php` y `utilidades/FirmaDigitalService.php`

---

## Ejecución con Docker

Este proyecto incluye soporte opcional para Docker. Para usarlo:

```bash
docker compose up -d --build
```

Luego abre:

```txt
http://localhost:8080
```

phpMyAdmin queda en:

```txt
http://localhost:8081
```

Más detalles en `DOCKER.md`.
