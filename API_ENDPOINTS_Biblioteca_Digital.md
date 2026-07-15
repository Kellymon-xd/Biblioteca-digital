# API REST - Biblioteca Digital

Esta sección documenta los endpoints disponibles para pruebas con Postman.  
La API funciona mediante el controlador:

```txt
controllers/ApiController.php
```

Todas las rutas usan el mismo punto de entrada:

```txt
{{baseUrl}}/index.php?mod=api&accion=...
```

Ejemplo local con XAMPP:

```txt
baseUrl = http://localhost/P/PHP/Biblioteca%20digital/public
```

Ejemplo en producción:

```txt
baseUrl = https://bibliotecadigital.kellymon.com
```

> Nota: para usar la API, en el archivo `.env` debe estar habilitada:
>
> ```env
> API_ENABLED=true
> ```

---

## Autenticación

La mayoría de endpoints requieren autenticación mediante token.

Primero se debe ejecutar el endpoint de login:

```http
POST {{baseUrl}}/index.php?mod=api&accion=login
```

Body JSON:

```json
{
  "username": "admin",
  "password": "root2514"
}
```

Respuesta esperada:

```json
{
  "token": "TOKEN_GENERADO",
  "usuario": {
    "id": 1,
    "username": "admin",
    "rol": "administrador"
  }
}
```

Luego, en los demás endpoints se debe enviar el header:

```http
Authorization: Bearer {{token}}
Accept: application/json
Content-Type: application/json
```

---

## Variables recomendadas en Postman

```txt
baseUrl = http://localhost/P/PHP/Biblioteca%20digital/public
token =
libroId = 1
reservaId = 1
usuarioId = 1
profesorId = 1
rolId = 1
categoriaId = 1
carreraId = 1
estudianteId = 1
solicitudId = 1
```

Script recomendado en el endpoint de login, pestaña **Scripts > Post-response**:

```javascript
const json = pm.response.json();

if (json.token) {
  pm.collectionVariables.set("token", json.token);
}

pm.test("Login correcto", function () {
  pm.response.to.have.status(200);
});
```

---

## Endpoints de autenticación

| Método | Endpoint | Descripción |
| --- | --- | --- |
| POST | `/index.php?mod=api&accion=login` | Inicia sesión y devuelve token |
| GET | `/index.php?mod=api&accion=perfil` | Muestra el usuario autenticado |
| POST | `/index.php?mod=api&accion=logout` | Cierra la sesión API |

---

## Dashboard y logs

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=dashboard` | Devuelve resumen general del sistema |
| GET | `/index.php?mod=api&accion=logs` | Lista registros de acceso y actividad |

---

## Libros

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=libros` | Lista libros |
| GET | `/index.php?mod=api&accion=libros&pag=1` | Lista libros con paginación |
| GET | `/index.php?mod=api&accion=libros&q=calculo` | Busca libros |
| GET | `/index.php?mod=api&accion=libros&id=1` | Consulta un libro por ID |
| POST | `/index.php?mod=api&accion=libros` | Crea un libro |
| PUT | `/index.php?mod=api&accion=libros&id={{libroId}}` | Actualiza un libro |
| PATCH | `/index.php?mod=api&accion=libros&id={{libroId}}` | Actualiza parcialmente un libro |
| DELETE | `/index.php?mod=api&accion=libros&id={{libroId}}` | Desactiva un libro |

Body para crear libro:

```json
{
  "titulo": "Libro creado desde API",
  "autor": "Autor API",
  "isbn": "API-2026-001",
  "id_categoria": 1,
  "costo": 12.50,
  "unidades_totales": 3,
  "activo": 1
}
```

Body para actualizar libro:

```json
{
  "titulo": "Libro actualizado desde API",
  "autor": "Autor API Editado",
  "isbn": "API-2026-001",
  "id_categoria": 1,
  "costo": 18.75,
  "unidades_totales": 5,
  "activo": 1
}
```

---

## Categorías

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=categorias` | Lista categorías |
| POST | `/index.php?mod=api&accion=categorias` | Crea una categoría |
| PUT | `/index.php?mod=api&accion=categorias&id={{categoriaId}}` | Actualiza una categoría |
| PATCH | `/index.php?mod=api&accion=categorias&id={{categoriaId}}` | Actualiza parcialmente una categoría |
| DELETE | `/index.php?mod=api&accion=categorias&id={{categoriaId}}` | Desactiva una categoría |

Body para crear categoría:

```json
{
  "nombre": "Categoría API",
  "descripcion": "Creada desde Postman",
  "activo": 1
}
```

---

## Carreras

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=carreras` | Lista carreras |
| POST | `/index.php?mod=api&accion=carreras` | Crea una carrera |
| PUT | `/index.php?mod=api&accion=carreras&id={{carreraId}}` | Actualiza una carrera |
| PATCH | `/index.php?mod=api&accion=carreras&id={{carreraId}}` | Actualiza parcialmente una carrera |
| DELETE | `/index.php?mod=api&accion=carreras&id={{carreraId}}` | Desactiva una carrera |

Body para crear carrera:

```json
{
  "codigo": "API",
  "nombre": "Carrera API",
  "descripcion": "Creada desde Postman",
  "activo": 1
}
```

---

## Estudiantes

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=estudiantes` | Lista estudiantes |
| GET | `/index.php?mod=api&accion=estudiantes&q=juan` | Busca estudiantes |
| GET | `/index.php?mod=api&accion=estudiantes&pag=1` | Lista estudiantes con paginación |
| POST | `/index.php?mod=api&accion=estudiantes` | Crea un estudiante |
| PUT | `/index.php?mod=api&accion=estudiantes&id={{estudianteId}}` | Actualiza un estudiante |
| PATCH | `/index.php?mod=api&accion=estudiantes&id={{estudianteId}}` | Actualiza parcialmente un estudiante |
| DELETE | `/index.php?mod=api&accion=estudiantes&id={{estudianteId}}` | Desactiva un estudiante |

Body para crear estudiante:

```json
{
  "cedula": "8-999-222",
  "primer_nombre": "Estudiante",
  "segundo_nombre": "",
  "primer_apellido": "API",
  "segundo_apellido": "Postman",
  "fecha_nacimiento": "2002-01-15",
  "email": "estudianteapi@correo.com",
  "telefono": "6000-0000",
  "id_carrera": 1,
  "password": "Estudiante123*",
  "activo": 1
}
```

---

## Profesores

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=profesores` | Lista profesores |
| GET | `/index.php?mod=api&accion=profesores&q=sistemas` | Busca profesores |
| POST | `/index.php?mod=api&accion=profesores` | Crea un profesor |
| PUT | `/index.php?mod=api&accion=profesores&id={{profesorId}}` | Actualiza un profesor |
| PATCH | `/index.php?mod=api&accion=profesores&id={{profesorId}}` | Actualiza parcialmente un profesor |
| DELETE | `/index.php?mod=api&accion=profesores&id={{profesorId}}` | Desactiva un profesor |

Body para crear profesor:

```json
{
  "cip": "8-999-111",
  "primer_nombre": "Carlos",
  "segundo_nombre": "",
  "primer_apellido": "Gomez",
  "segundo_apellido": "",
  "fecha_nacimiento": "1990-05-12",
  "email": "profesorapi@correo.com",
  "departamento": "Sistemas",
  "especialidad": "Programación",
  "password": "Profesor123*",
  "activo": 1
}
```

---

## Reservas

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=reservas` | Lista reservas |
| GET | `/index.php?mod=api&accion=reservas&q=pendiente` | Busca reservas |
| GET | `/index.php?mod=api&accion=reservas&id={{reservaId}}` | Consulta una reserva por ID |
| POST | `/index.php?mod=api&accion=reservas` | Crea una reserva |
| DELETE | `/index.php?mod=api&accion=reservas&id={{reservaId}}` | Registra devolución |

Body para crear reserva:

```json
{
  "id_estudiante": 1,
  "id_libro": 1,
  "fecha_devolucion_esperada": "2026-07-25"
}
```

> En reservas, el método `DELETE` no elimina físicamente el registro; se utiliza para registrar la devolución.

---

## Solicitudes

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=solicitudes` | Lista solicitudes |
| GET | `/index.php?mod=api&accion=solicitudes&q=calculo` | Busca solicitudes |
| GET | `/index.php?mod=api&accion=solicitudes&pag=1` | Lista solicitudes con paginación |
| POST | `/index.php?mod=api&accion=solicitudes` | Crea una solicitud |
| PATCH | `/index.php?mod=api&accion=solicitudes&id={{solicitudId}}` | Responde una solicitud |

Body para crear solicitud:

```json
{
  "titulo": "Libro solicitado desde API",
  "autor": "Autor solicitado",
  "motivo": "Material requerido para clase"
}
```

Body para responder solicitud:

```json
{
  "estado": "APROBADA",
  "observaciones": "Solicitud aprobada desde la API."
}
```

---

## Usuarios administrativos

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=usuarios` | Lista usuarios |
| GET | `/index.php?mod=api&accion=usuarios&q=admin` | Busca usuarios |
| GET | `/index.php?mod=api&accion=usuarios&id={{usuarioId}}` | Consulta un usuario por ID |
| POST | `/index.php?mod=api&accion=usuarios` | Crea un usuario |
| PUT | `/index.php?mod=api&accion=usuarios&id={{usuarioId}}` | Actualiza un usuario |
| PATCH | `/index.php?mod=api&accion=usuarios&id={{usuarioId}}` | Actualiza parcialmente un usuario |
| DELETE | `/index.php?mod=api&accion=usuarios&id={{usuarioId}}` | Desactiva un usuario |

Body para crear usuario:

```json
{
  "nombre": "Api",
  "apellido": "Tester",
  "email": "apitester@correo.com",
  "username": "apitester",
  "password": "ApiTester123*",
  "rol": "operador",
  "activo": 1
}
```

---

## Roles

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=roles` | Lista roles |
| POST | `/index.php?mod=api&accion=roles` | Crea un rol |
| PUT | `/index.php?mod=api&accion=roles&id={{rolId}}` | Actualiza un rol |
| PATCH | `/index.php?mod=api&accion=roles&id={{rolId}}` | Actualiza parcialmente un rol |
| DELETE | `/index.php?mod=api&accion=roles&id={{rolId}}` | Desactiva un rol |

Body para crear rol:

```json
{
  "nombre": "Auxiliar API",
  "descripcion": "Rol creado desde Postman",
  "modulos": "dashboard,libros,reservas",
  "activo": 1
}
```

---

## Configuración

| Método | Endpoint | Descripción |
| --- | --- | --- |
| GET | `/index.php?mod=api&accion=configuracion` | Consulta parámetros de configuración |
| PUT | `/index.php?mod=api&accion=configuracion` | Actualiza configuración |
| PATCH | `/index.php?mod=api&accion=configuracion` | Actualiza parcialmente configuración |

Body para actualizar configuración:

```json
{
  "dias_prestamo_estudiante": 3,
  "dias_prestamo_profesor": 5
}
```

---

## Códigos de respuesta comunes

| Código | Significado |
| --- | --- |
| 200 | Solicitud correcta |
| 201 | Registro creado |
| 400 | Datos inválidos o incompletos |
| 401 | Autenticación requerida |
| 403 | No autorizado por permisos |
| 404 | Endpoint o registro no encontrado |
| 500 | Error interno del servidor |

---

## Orden recomendado para demostración

1. `POST login`
2. `GET perfil`
3. `GET dashboard`
4. `GET libros`
5. `POST libros`
6. `PUT libros`
7. `GET categorias`
8. `POST categorias`
9. `GET estudiantes`
10. `GET profesores`
11. `GET reservas`
12. `POST reservas`
13. `DELETE reservas`
14. `GET solicitudes`
15. `PATCH solicitudes`
16. `GET usuarios`
17. `GET roles`
18. `GET configuracion`
19. `POST logout`
