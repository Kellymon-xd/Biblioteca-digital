# Biblioteca Digital

Proyecto PHP MVC para gestion de biblioteca digital con MySQL/phpMyAdmin.

## Requisitos

- PHP 8.1 o superior
- MySQL/MariaDB
- Apache local con XAMPP o Laragon
- Extensiones PHP: PDO MySQL, GD, mbstring

## Instalacion rapida

1. Copia el proyecto dentro del directorio publico de Apache, por ejemplo `htdocs/Biblioteca digital`.
2. En phpMyAdmin entra con un usuario administrador, normalmente `root`.
3. Importa el script [conexion/biblioteca_digital.sql](conexion/biblioteca_digital.sql).
4. El script crea la base de datos `biblioteca_digital` y el usuario de aplicacion `bib_app`.
5. Abre el sistema en `http://localhost/Biblioteca%20digital/`.

## Credenciales de prueba

| Rol | Usuario | Contrasena | Acceso |
| --- | --- | --- | --- |
| Administrador | `admin` | `root2514` | Panel administrativo |
| Estudiante | `user@correo.com` o `8-888-888` | `User123*` | Portal estudiantil |

## Modulos implementados

- Login administrativo con CSRF, bitacora de IP/fecha/agente e intento fallido.
- Bloqueo automatico al tercer intento fallido.
- CRUD de usuarios, estudiantes, carreras, categorias y libros.
- Validacion de contrasenas de 8 a 12 caracteres.
- Validacion y sanitizacion centralizadas en clases.
- Carga de imagenes de libros y generacion de thumbnail.
- Reservas estudiantiles con descuento y devolucion de inventario.
- Solicitudes de libros no disponibles.
- Estadisticas de libros mas reservados por periodo.
- Exportacion CSV compatible con Excel para el reporte de libros.
- Firma digital HMAC para registros criticos y tabla de auditoria.

## Archivos clave

- Conexion PDO: [conexion/Conexion.php](conexion/Conexion.php)
- Script SQL: [conexion/biblioteca_digital.sql](conexion/biblioteca_digital.sql)
- CSRF: [utilidades/CsrfToken.php](utilidades/CsrfToken.php)
- Validacion: [utilidades/Validador.php](utilidades/Validador.php)
- Sanitizacion: [utilidades/Sanitizador.php](utilidades/Sanitizador.php)
- Firma digital: [utilidades/FirmaDigital.php](utilidades/FirmaDigital.php)
