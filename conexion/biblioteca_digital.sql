-- ============================================================
--  BIBLIOTECA DIGITAL  |  Script de Base de Datos
--  Versión  : 1.0.0
--  Charset  : utf8mb4 / utf8mb4_unicode_ci
--  Motor    : InnoDB
--  Fecha    : 2025
-- ============================================================
--  INSTRUCCIONES DE USO
--  1. Ejecuta este script conectado como root en phpMyAdmin
--     o desde la CLI:  mysql -u root -p < biblioteca_digital.sql
--  2. El usuario de la aplicación web se crea automáticamente.
--  3. Actualiza Conexion.php con las credenciales del nuevo usuario.
-- ============================================================

-- ============================================================
-- 0. BASE DE DATOS
-- ============================================================
DROP DATABASE IF EXISTS biblioteca_digital;
CREATE DATABASE biblioteca_digital
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE biblioteca_digital;

-- ============================================================
-- 1. USUARIO DE APLICACIÓN (Principio de Mínimo Privilegio)
--    NO usa root; solo tiene los permisos necesarios para
--    el funcionamiento del sitio web.
-- ============================================================
DROP USER IF EXISTS 'bib_app'@'localhost';
CREATE USER 'bib_app'@'localhost'
    IDENTIFIED BY 'B!bl10t3c@_S3cur3#2025'
    PASSWORD EXPIRE NEVER;

-- Permisos estrictamente necesarios (sin CREATE/DROP/ALTER)
GRANT SELECT, INSERT, UPDATE, DELETE
    ON biblioteca_digital.*
    TO 'bib_app'@'localhost';

FLUSH PRIVILEGES;

-- ============================================================
-- 2. TABLA: carreras
-- ============================================================
CREATE TABLE carreras (
    id_carrera      INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(150)     NOT NULL,
    codigo          VARCHAR(20)      NOT NULL,
    descripcion     TEXT,
    activo          TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                              ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_carrera),
    UNIQUE KEY uq_carrera_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Carreras universitarias disponibles';

-- ============================================================
-- 3. TABLA: usuarios  (administradores / personal)
-- ============================================================
CREATE TABLE usuarios (
    id_usuario          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre              VARCHAR(100)    NOT NULL,
    apellido            VARCHAR(100)    NOT NULL,
    email               VARCHAR(255)    NOT NULL,
    username            VARCHAR(50)     NOT NULL,
    password_hash       VARCHAR(255)    NOT NULL COMMENT 'bcrypt cost>=12',
    rol                 ENUM('administrador','operador')
                                        NOT NULL DEFAULT 'operador',
    activo              TINYINT(1)      NOT NULL DEFAULT 1,
    intentos_fallidos   TINYINT(1)      NOT NULL DEFAULT 0,
    bloqueado           TINYINT(1)      NOT NULL DEFAULT 0,
    ultimo_login        DATETIME,
    firma_datos         CHAR(64)        COMMENT 'HMAC-SHA256 para integridad del registro',
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                 ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_usuario),
    UNIQUE KEY uq_usuario_email    (email),
    UNIQUE KEY uq_usuario_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Usuarios administrativos del sistema';

-- ============================================================
-- 4. TABLA: login_logs  (bitácora de auditoría OWASP)
-- ============================================================
CREATE TABLE login_logs (
    id_log          INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    username        VARCHAR(50),
    ip_address      VARCHAR(45)     NOT NULL COMMENT 'Soporta IPv4 e IPv6',
    user_agent      VARCHAR(500),
    accion          ENUM('LOGIN_EXITOSO','LOGIN_FALLIDO',
                         'CIERRE_SESION','CUENTA_BLOQUEADA')
                                    NOT NULL,
    descripcion     VARCHAR(500),
    id_usuario      INT UNSIGNED    COMMENT 'NULL si el usuario no existe',
    fecha           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_log),
    KEY idx_log_username  (username),
    KEY idx_log_ip        (ip_address),
    KEY idx_log_fecha     (fecha),
    CONSTRAINT fk_log_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Bitácora de intentos de acceso al sistema';

ALTER TABLE login_logs
ADD COLUMN tipo_actor VARCHAR(20) NULL AFTER id_usuario,
ADD COLUMN id_estudiante INT NULL AFTER tipo_actor,
ADD COLUMN identificador VARCHAR(100) NULL AFTER id_estudiante;

-- ============================================================
-- 5. TABLA: estudiantes
-- ============================================================
CREATE TABLE estudiantes (
    id_estudiante       INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    cip                 VARCHAR(20)     NOT NULL COMMENT 'Cédula / ID único del estudiante',
    primer_nombre       VARCHAR(100)    NOT NULL,
    segundo_nombre      VARCHAR(100),
    primer_apellido     VARCHAR(100)    NOT NULL,
    segundo_apellido    VARCHAR(100),
    fecha_nacimiento    DATE            NOT NULL,
    id_carrera          INT UNSIGNED    NOT NULL,
    email               VARCHAR(255),
    password_hash       VARCHAR(255)    COMMENT 'bcrypt para acceso portal estudiante',
    activo              TINYINT(1)      NOT NULL DEFAULT 1,
    intentos_fallidos   TINYINT(1)      NOT NULL DEFAULT 0,
    bloqueado           TINYINT(1)      NOT NULL DEFAULT 0,
    ultimo_login        DATETIME,
    created_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                 ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_estudiante),
    UNIQUE KEY uq_estudiante_cip   (cip),       -- RF: No duplicar cédula
    UNIQUE KEY uq_estudiante_email (email),
    CONSTRAINT fk_estudiante_carrera
        FOREIGN KEY (id_carrera) REFERENCES carreras(id_carrera)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de estudiantes matriculados';

-- ============================================================
-- 6. TABLA: categorias_libros
-- ============================================================
CREATE TABLE categorias_libros (
    id_categoria    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    nombre          VARCHAR(100)    NOT NULL,
    descripcion     TEXT,
    activo          TINYINT(1)      NOT NULL DEFAULT 1,
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_categoria),
    UNIQUE KEY uq_categoria_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Categorías de clasificación de libros';

-- ============================================================
-- 7. TABLA: libros
-- ============================================================
CREATE TABLE libros (
    id_libro                INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    isbn                    VARCHAR(20),
    titulo                  VARCHAR(300)    NOT NULL,
    autor                   VARCHAR(200)    NOT NULL,
    editorial               VARCHAR(150),
    anio_publicacion        YEAR,
    id_categoria            INT UNSIGNED    NOT NULL,
    descripcion             TEXT,
    unidades_totales        INT UNSIGNED    NOT NULL DEFAULT 0,
    unidades_disponibles    INT UNSIGNED    NOT NULL DEFAULT 0,
    imagen_original         VARCHAR(500)    COMMENT 'Ruta relativa imagen original  /uploads/libros/orig/',
    imagen_thumb            VARCHAR(500)    COMMENT 'Ruta relativa thumbnail        /uploads/libros/thumb/',
    activo                  TINYINT(1)      NOT NULL DEFAULT 1,
    firma_datos             CHAR(64)        COMMENT 'HMAC-SHA256 que protege unidades_totales y unidades_disponibles',
    created_at              DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                     ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_libro),
    UNIQUE KEY uq_libro_isbn (isbn),
    KEY idx_libro_titulo     (titulo(50)),
    CONSTRAINT fk_libro_categoria
        FOREIGN KEY (id_categoria) REFERENCES categorias_libros(id_categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Inventario de libros de la biblioteca';

-- ============================================================
-- 8. TABLA: reservas  (préstamos de libros a estudiantes)
-- ============================================================
CREATE TABLE reservas (
    id_reserva                  INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_estudiante               INT UNSIGNED    NOT NULL,
    id_libro                    INT UNSIGNED    NOT NULL,
    fecha_reserva               DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_devolucion_esperada   DATE            NOT NULL,
    fecha_devolucion_real       DATE            COMMENT 'NULL mientras el libro no se devuelva',
    estado                      ENUM('PENDIENTE','ACTIVA','DEVUELTA','CANCELADA')
                                                NOT NULL DEFAULT 'PENDIENTE',
    firma_datos                 CHAR(64)        COMMENT 'HMAC-SHA256 sello de integridad de la reserva',
    created_at                  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at                  DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                         ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_reserva),
    KEY idx_reserva_estado    (estado),
    KEY idx_reserva_fecha     (fecha_reserva),
    CONSTRAINT fk_reserva_estudiante
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante),
    CONSTRAINT fk_reserva_libro
        FOREIGN KEY (id_libro)      REFERENCES libros(id_libro)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Historial de préstamos y devoluciones';

-- ============================================================
-- 9. TABLA: solicitudes_libros
--    Permite al estudiante pedir títulos que no están en stock
-- ============================================================
CREATE TABLE solicitudes_libros (
    id_solicitud    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    id_estudiante   INT UNSIGNED    NOT NULL,
    titulo          VARCHAR(300)    NOT NULL,
    autor           VARCHAR(200),
    area            ENUM('Matemáticas','Ciencias','Tecnologías',
                         'Deporte','Salud','Revistas Científicas')
                                    NOT NULL,
    descripcion     TEXT,
    estado          ENUM('PENDIENTE','REVISADA','APROBADA','RECHAZADA')
                                    NOT NULL DEFAULT 'PENDIENTE',
    observaciones   TEXT            COMMENT 'Respuesta del administrador',
    created_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP
                                             ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_solicitud),
    CONSTRAINT fk_solicitud_estudiante
        FOREIGN KEY (id_estudiante) REFERENCES estudiantes(id_estudiante)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Solicitudes de compra enviadas por estudiantes';

-- ============================================================
-- 10. TABLA: auditoria_firmas
--     Rastrea cambios en registros críticos (sello de integridad)
-- ============================================================
CREATE TABLE auditoria_firmas (
    id_auditoria    INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    tabla_afectada  VARCHAR(50)     NOT NULL,
    id_registro     INT UNSIGNED    NOT NULL,
    accion          ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    firma_anterior  CHAR(64),
    firma_nueva     CHAR(64),
    id_usuario      INT UNSIGNED,
    ip_address      VARCHAR(45),
    fecha           DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_auditoria),
    CONSTRAINT fk_auditoria_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
  COMMENT='Registro de integridad HMAC para datos críticos';

-- ============================================================
-- 11. DATOS SEMILLA (seed data)
-- ============================================================

-- Carreras
INSERT INTO carreras (nombre, codigo, descripcion) VALUES
('Licenciatura en Informática',   'LI-001', 'Sistemas de información y programación'),
('Ingeniería en Sistemas',        'IS-001', 'Ingeniería de software y redes'),
('Administración de Empresas',    'AE-001', 'Gestión y administración empresarial'),
('Contabilidad',                  'CO-001', 'Contaduría y finanzas'),
('Ingeniería Civil',              'IC-001', 'Diseño y construcción de infraestructura'),
('Medicina',                      'ME-001', 'Ciencias de la salud'),
('Derecho',                       'DE-001', 'Ciencias jurídicas y políticas');

-- Categorías de libros
INSERT INTO categorias_libros (nombre, descripcion) VALUES
('Química',      'Química general, orgánica e inorgánica'),
('Sistemas',     'Informática, redes y desarrollo de software'),
('Lógica',       'Lógica matemática y computacional'),
('Matemática',   'Cálculo, álgebra lineal y geometría'),
('Estadística',  'Estadística descriptiva, inferencial y probabilidad');

-- ============================================================
--  USUARIO ADMINISTRADOR
--  username : admin
--  password : root2514   (hash bcrypt cost 12)
--
--  Hash generado con:
--    PHP  → password_hash('root2514', PASSWORD_BCRYPT, ['cost'=>12])
--    Python bcrypt → bcrypt.hashpw(b'root2514', bcrypt.gensalt(12))
--
--  NOTA: $2b$ (Python) y $2y$ (PHP) son intercambiables en password_verify()
-- ============================================================
INSERT INTO usuarios
    (nombre, apellido, email, username, password_hash, rol)
VALUES (
    'Administrador',
    'General',
    'admin@biblioteca.edu.pa',
    'admin',
    '$2b$12$bTg81wKj7pALs826U1bf5eBD.cDOD.viZ35ERML6CUOWrJJ.g0WeO',
    'admin'
);

-- Estudiante de prueba para el portal
-- identificador: 8-888-888
-- email        : user@correo.com
-- password     : User123*
INSERT INTO estudiantes
    (cip, primer_nombre, segundo_nombre, primer_apellido, segundo_apellido,
     fecha_nacimiento, id_carrera, email, password_hash)
VALUES (
    '8-888-888',
    'Usuario',
    NULL,
    'Estudiante',
    NULL,
    '2002-01-15',
    1,
    'user@correo.com',
    '$2y$12$SUIkZIiL3NH96bcWdn9VCe7zxwNAChYub.71gCuLWcbEsTc2v651u'
);

-- Libros de ejemplo
INSERT INTO libros (
    isbn, titulo, autor, editorial, anio_publicacion,
    id_categoria, descripcion, unidades_totales, unidades_disponibles,
    imagen_original, imagen_thumb, firma_datos
) VALUES
('978-0-13-110362-7', 'El Lenguaje de Programación C',
 'Brian W. Kernighan & Dennis M. Ritchie', 'Prentice Hall', 1988,
 2, 'La referencia clásica del lenguaje C.', 5, 5,
 'uploads/libros/orig/lenguaje_programacion_c.jpg',
 'uploads/libros/thumb/lenguaje_programacion_c.jpg',
 'c204e483fc015014be58cceae21971bcfa12ea9f8f01e015d9d0f824bbc6b5b8'),

('978-607-32-0122-6', 'Álgebra Lineal con Aplicaciones',
 'Stanley Grossman', 'McGraw-Hill', 2012,
 4, 'Texto universitario estándar de álgebra lineal.', 3, 3,
 'uploads/libros/orig/algebra_lineal_grossman.jpg',
 'uploads/libros/thumb/algebra_lineal_grossman.jpg',
 '451c3f15637f88ab22d5581eea54b24c22475568e3d0a966f93cc9291f254526'),

('978-84-9732-542-3', 'Probabilidad y Estadística para Ingeniería',
 'Murray R. Spiegel', 'McGraw-Hill', 2009,
 5, 'Serie Schaum. Incluye 897 problemas resueltos.', 4, 4,
 'uploads/libros/orig/probabilidad_estadistica_ingenieria.jpg',
 'uploads/libros/thumb/probabilidad_estadistica_ingenieria.jpg',
 'cf575c1ce13dffce7645f737c7c49131951ba142819af1714f6f1da52cbc8419'),

('978-0-596-51774-8', 'JavaScript: The Good Parts',
 'Douglas Crockford', 'O''Reilly Media', 2008,
 2, 'Las partes esenciales del lenguaje JavaScript.', 2, 2,
 'uploads/libros/orig/javascript_good_parts.jpg',
 'uploads/libros/thumb/javascript_good_parts.jpg',
 'b9129d716ffcfba71673840d6e0c974980eeb35162bf8b874ec179a1ed5b4408'),

('978-84-415-3927-4', 'Química General',
 'Linus Pauling', 'Reverté', 2014,
 1, 'Fundamentos de química para ciencias e ingeniería.', 6, 6,
 'uploads/libros/orig/quimica_general_pauling.jpg',
 'uploads/libros/thumb/quimica_general_pauling.jpg',
 'c931c7287ad4fb548c75d14a9ec47c5d4b4d80520bf394d4b477347b3e8b4724');
-- ============================================================
-- FIN DEL SCRIPT
-- ============================================================
-- Verificación rápida
SELECT 'BASE DE DATOS CREADA EXITOSAMENTE' AS estado;
SELECT TABLE_NAME AS tabla, TABLE_ROWS AS filas_aprox
FROM   information_schema.TABLES
WHERE  TABLE_SCHEMA = 'biblioteca_digital'
ORDER  BY TABLE_NAME;
