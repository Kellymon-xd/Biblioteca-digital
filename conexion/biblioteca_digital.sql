-- ============================================================
-- BIBLIOTECA DIGITAL | BASE COMPLETA NUEVOS REQUISITOS
-- Compatible con phpMyAdmin, XAMPP y Docker
-- Ejecutar con root. Este script borra y crea la base.
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "-05:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP DATABASE IF EXISTS `biblioteca_digital`;

CREATE DATABASE `biblioteca_digital`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- 1. ROLES
-- ============================================================

CREATE TABLE `biblioteca_digital`.`roles` (
    `id_rol`      INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(80) NOT NULL,
    `descripcion` VARCHAR(255),
    `modulos`     TEXT NOT NULL COMMENT 'CSV de módulos permitidos o * para control total',
    `activo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_rol`),
    UNIQUE KEY `uq_roles_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 2. CARRERAS
-- ============================================================

CREATE TABLE `biblioteca_digital`.`carreras` (
    `id_carrera`  INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`      VARCHAR(150) NOT NULL,
    `codigo`      VARCHAR(20) NOT NULL,
    `descripcion` TEXT,
    `activo`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_carrera`),
    UNIQUE KEY `uq_carrera_codigo` (`codigo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 3. USUARIOS ADMINISTRATIVOS
-- ============================================================

CREATE TABLE `biblioteca_digital`.`usuarios` (
    `id_usuario`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`            VARCHAR(100) NOT NULL,
    `apellido`          VARCHAR(100) NOT NULL,
    `email`             VARCHAR(255) NOT NULL,
    `username`          VARCHAR(50) NOT NULL,
    `password_hash`     VARCHAR(255) NOT NULL,
    `rol`               ENUM('administrador','operador') NOT NULL DEFAULT 'operador',
    `id_rol`            INT UNSIGNED NULL,
    `activo`            TINYINT(1) NOT NULL DEFAULT 1,
    `intentos_fallidos` TINYINT(1) NOT NULL DEFAULT 0,
    `bloqueado`         TINYINT(1) NOT NULL DEFAULT 0,
    `ultimo_login`      DATETIME NULL,
    `firma_datos`       CHAR(64),
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_usuario`),
    UNIQUE KEY `uq_usuario_email` (`email`),
    UNIQUE KEY `uq_usuario_username` (`username`),
    KEY `idx_usuario_rol` (`id_rol`),
    CONSTRAINT `fk_usuario_rol`
        FOREIGN KEY (`id_rol`) REFERENCES `biblioteca_digital`.`roles` (`id_rol`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 4. ESTUDIANTES
-- ============================================================

CREATE TABLE `biblioteca_digital`.`estudiantes` (
    `id_estudiante`     INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cip`               VARCHAR(20) NOT NULL,
    `primer_nombre`     VARCHAR(100) NOT NULL,
    `segundo_nombre`    VARCHAR(100),
    `primer_apellido`   VARCHAR(100) NOT NULL,
    `segundo_apellido`  VARCHAR(100),
    `fecha_nacimiento`  DATE NOT NULL,
    `id_carrera`        INT UNSIGNED NOT NULL,
    `email`             VARCHAR(255),
    `password_hash`     VARCHAR(255),
    `activo`            TINYINT(1) NOT NULL DEFAULT 1,
    `intentos_fallidos` TINYINT(1) NOT NULL DEFAULT 0,
    `bloqueado`         TINYINT(1) NOT NULL DEFAULT 0,
    `ultimo_login`      DATETIME NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_estudiante`),
    UNIQUE KEY `uq_estudiante_cip` (`cip`),
    UNIQUE KEY `uq_estudiante_email` (`email`),
    KEY `idx_estudiante_carrera` (`id_carrera`),
    CONSTRAINT `fk_estudiante_carrera`
        FOREIGN KEY (`id_carrera`) REFERENCES `biblioteca_digital`.`carreras` (`id_carrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 5. PROFESORES / DOCENTES
-- ============================================================

CREATE TABLE `biblioteca_digital`.`profesores` (
    `id_profesor`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `cip`               VARCHAR(20) NOT NULL,
    `primer_nombre`     VARCHAR(100) NOT NULL,
    `segundo_nombre`    VARCHAR(100),
    `primer_apellido`   VARCHAR(100) NOT NULL,
    `segundo_apellido`  VARCHAR(100),
    `fecha_nacimiento`  DATE NULL,
    `email`             VARCHAR(255),
    `departamento`      VARCHAR(150),
    `especialidad`      VARCHAR(150),
    `password_hash`     VARCHAR(255),
    `activo`            TINYINT(1) NOT NULL DEFAULT 1,
    `intentos_fallidos` TINYINT(1) NOT NULL DEFAULT 0,
    `bloqueado`         TINYINT(1) NOT NULL DEFAULT 0,
    `ultimo_login`      DATETIME NULL,
    `created_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_profesor`),
    UNIQUE KEY `uq_profesor_cip` (`cip`),
    UNIQUE KEY `uq_profesor_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 6. CATEGORÍAS DE LIBROS
-- ============================================================

CREATE TABLE `biblioteca_digital`.`categorias_libros` (
    `id_categoria` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`       VARCHAR(100) NOT NULL,
    `descripcion`  TEXT,
    `activo`       TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_categoria`),
    UNIQUE KEY `uq_categoria_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 7. LIBROS
-- ============================================================

CREATE TABLE `biblioteca_digital`.`libros` (
    `id_libro`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `isbn`                 VARCHAR(20),
    `titulo`               VARCHAR(300) NOT NULL,
    `autor`                VARCHAR(200) NOT NULL,
    `editorial`            VARCHAR(150),
    `anio_publicacion`     YEAR,
    `id_categoria`         INT UNSIGNED NOT NULL,
    `descripcion`          TEXT,
    `temas`                VARCHAR(300),
    `costo`                DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `unidades_totales`     INT UNSIGNED NOT NULL DEFAULT 0,
    `unidades_disponibles` INT UNSIGNED NOT NULL DEFAULT 0,
    `imagen_original`      VARCHAR(500),
    `imagen_thumb`         VARCHAR(500),
    `activo`               TINYINT(1) NOT NULL DEFAULT 1,
    `firma_datos`          CHAR(64),
    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_libro`),
    UNIQUE KEY `uq_libro_isbn` (`isbn`),
    KEY `idx_libro_titulo` (`titulo`(80)),
    KEY `idx_libro_autor` (`autor`(80)),
    KEY `idx_libro_categoria` (`id_categoria`),
    CONSTRAINT `fk_libro_categoria`
        FOREIGN KEY (`id_categoria`) REFERENCES `biblioteca_digital`.`categorias_libros` (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 8. PARÁMETROS DINÁMICOS
-- ============================================================

CREATE TABLE `biblioteca_digital`.`parametros` (
    `clave`      VARCHAR(100) NOT NULL,
    `valor`      VARCHAR(255) NOT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 9. RESERVAS / PRÉSTAMOS
-- ============================================================

CREATE TABLE `biblioteca_digital`.`reservas` (
    `id_reserva`                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tipo_actor`                ENUM('ESTUDIANTE','PROFESOR','ADMINISTRATIVO') NOT NULL DEFAULT 'ESTUDIANTE',
    `id_estudiante`             INT UNSIGNED NULL,
    `id_profesor`               INT UNSIGNED NULL,
    `id_usuario`                INT UNSIGNED NULL,
    `id_libro`                  INT UNSIGNED NOT NULL,
    `fecha_reserva`             DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `dias_prestamo`             INT UNSIGNED NOT NULL DEFAULT 3,
    `fecha_devolucion_esperada` DATE NOT NULL,
    `fecha_devolucion_real`     DATE NULL,
    `estado`                    ENUM('PENDIENTE','ACTIVA','DEVUELTA','CANCELADA') NOT NULL DEFAULT 'ACTIVA',
    `firma_datos`               CHAR(64),
    `created_at`                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`                DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_reserva`),
    KEY `idx_reserva_estado` (`estado`),
    KEY `idx_reserva_fecha` (`fecha_reserva`),
    KEY `idx_reserva_libro` (`id_libro`),
    KEY `idx_reserva_estudiante` (`id_estudiante`),
    KEY `idx_reserva_profesor` (`id_profesor`),
    KEY `idx_reserva_usuario` (`id_usuario`),
    CONSTRAINT `fk_reserva_estudiante`
        FOREIGN KEY (`id_estudiante`) REFERENCES `biblioteca_digital`.`estudiantes` (`id_estudiante`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_reserva_profesor`
        FOREIGN KEY (`id_profesor`) REFERENCES `biblioteca_digital`.`profesores` (`id_profesor`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_reserva_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `biblioteca_digital`.`usuarios` (`id_usuario`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_reserva_libro`
        FOREIGN KEY (`id_libro`) REFERENCES `biblioteca_digital`.`libros` (`id_libro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 10. SOLICITUDES DE LIBROS NO EXISTENTES
-- ============================================================

CREATE TABLE `biblioteca_digital`.`solicitudes_libros` (
    `id_solicitud`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_estudiante`        INT UNSIGNED NULL,
    `id_profesor`          INT UNSIGNED NULL,
    `titulo`               VARCHAR(300) NOT NULL,
    `autor`                VARCHAR(200),
    `area`                 ENUM('Matemáticas','Ciencias','Tecnologías','Deporte','Salud','Revistas Científicas','Sistemas','Lógica','Química','Estadística') NOT NULL,
    `materia`              VARCHAR(150),
    `motivo`               VARCHAR(255),
    `tipo_solicitud`       ENUM('COMPRA','INTERBIBLIOTECARIO') NOT NULL DEFAULT 'COMPRA',
    `institucion_sugerida` VARCHAR(200),
    `descripcion`          TEXT,
    `estado`               ENUM('PENDIENTE','REVISADA','APROBADA','RECHAZADA') NOT NULL DEFAULT 'PENDIENTE',
    `observaciones`        TEXT,
    `created_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`           DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_solicitud`),
    KEY `idx_solicitud_estudiante` (`id_estudiante`),
    KEY `idx_solicitud_profesor` (`id_profesor`),
    KEY `idx_solicitud_estado` (`estado`),
    CONSTRAINT `fk_solicitud_estudiante`
        FOREIGN KEY (`id_estudiante`) REFERENCES `biblioteca_digital`.`estudiantes` (`id_estudiante`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_solicitud_profesor`
        FOREIGN KEY (`id_profesor`) REFERENCES `biblioteca_digital`.`profesores` (`id_profesor`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 11. LOGIN LOGS / BITÁCORA
-- ============================================================

CREATE TABLE `biblioteca_digital`.`login_logs` (
    `id_log`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(100),
    `ip_address`    VARCHAR(45) NOT NULL,
    `user_agent`    VARCHAR(500),
    `accion`        ENUM('LOGIN_EXITOSO','LOGIN_FALLIDO','CIERRE_SESION','CUENTA_BLOQUEADA') NOT NULL,
    `descripcion`   VARCHAR(500),
    `id_usuario`    INT UNSIGNED NULL,
    `tipo_actor`    VARCHAR(20) NULL,
    `id_estudiante` INT UNSIGNED NULL,
    `id_profesor`   INT UNSIGNED NULL,
    `identificador` VARCHAR(100) NULL,
    `fecha`         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_log`),
    KEY `idx_log_username` (`username`),
    KEY `idx_log_ip` (`ip_address`),
    KEY `idx_log_fecha` (`fecha`),
    KEY `idx_log_usuario` (`id_usuario`),
    KEY `idx_log_estudiante` (`id_estudiante`),
    KEY `idx_log_profesor` (`id_profesor`),
    CONSTRAINT `fk_log_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `biblioteca_digital`.`usuarios` (`id_usuario`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_log_estudiante`
        FOREIGN KEY (`id_estudiante`) REFERENCES `biblioteca_digital`.`estudiantes` (`id_estudiante`)
        ON DELETE SET NULL,
    CONSTRAINT `fk_log_profesor`
        FOREIGN KEY (`id_profesor`) REFERENCES `biblioteca_digital`.`profesores` (`id_profesor`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 12. AUDITORÍA DE FIRMAS
-- ============================================================

CREATE TABLE `biblioteca_digital`.`auditoria_firmas` (
    `id_auditoria`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `tabla_afectada` VARCHAR(50) NOT NULL,
    `id_registro`    INT UNSIGNED NOT NULL,
    `accion`         ENUM('INSERT','UPDATE','DELETE') NOT NULL,
    `firma_anterior` CHAR(64),
    `firma_nueva`    CHAR(64),
    `id_usuario`     INT UNSIGNED NULL,
    `ip_address`     VARCHAR(45),
    `fecha`          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_auditoria`),
    KEY `idx_auditoria_tabla` (`tabla_afectada`),
    KEY `idx_auditoria_fecha` (`fecha`),
    CONSTRAINT `fk_auditoria_usuario`
        FOREIGN KEY (`id_usuario`) REFERENCES `biblioteca_digital`.`usuarios` (`id_usuario`)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- 13. DATOS SEMILLA
-- ============================================================

INSERT INTO `biblioteca_digital`.`roles` (`id_rol`, `nombre`, `descripcion`, `modulos`, `activo`) VALUES
(1, 'Administrador', 'Control total del sistema', '*', 1),
(2, 'Bibliotecario', 'Gestión de catálogo, reservas, estudiantes, docentes y solicitudes', 'dashboard,estudiantes,profesores,carreras,categorias,libros,reservas,solicitudes,logs,configuracion', 1),
(3, 'Consulta', 'Solo reportes y consultas básicas', 'dashboard,libros,reservas,solicitudes', 1);

INSERT INTO `biblioteca_digital`.`carreras` (`nombre`, `codigo`, `descripcion`) VALUES
('Licenciatura en Informática', 'LI-001', 'Sistemas de información y programación'),
('Ingeniería en Sistemas', 'IS-001', 'Ingeniería de software y redes'),
('Administración de Empresas', 'AE-001', 'Gestión y administración empresarial'),
('Contabilidad', 'CO-001', 'Contaduría y finanzas'),
('Ingeniería Civil', 'IC-001', 'Diseño y construcción de infraestructura'),
('Medicina', 'ME-001', 'Ciencias de la salud'),
('Derecho', 'DE-001', 'Ciencias jurídicas y políticas');

INSERT INTO `biblioteca_digital`.`categorias_libros` (`nombre`, `descripcion`) VALUES
('Química', 'Química general, orgánica e inorgánica'),
('Sistemas', 'Informática, redes y desarrollo de software'),
('Lógica', 'Lógica matemática y computacional'),
('Matemática', 'Cálculo, álgebra lineal y geometría'),
('Estadística', 'Estadística descriptiva, inferencial y probabilidad');

-- Admin: usuario admin / contraseña root2514
INSERT INTO `biblioteca_digital`.`usuarios` (
    `nombre`, `apellido`, `email`, `username`, `password_hash`, `rol`, `id_rol`
) VALUES (
    'Administrador',
    'General',
    'admin@biblioteca.edu.pa',
    'admin',
    '$2y$12$FgmoLCa78lH.93w.Zw8oOe0f9tHfkn8XQHsfhJ4jvOuAGK.iSb3WC',
    'administrador',
    1
);

-- Estudiante: email user@correo.com / contraseña User123*
INSERT INTO `biblioteca_digital`.`estudiantes` (
    `cip`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`,
    `fecha_nacimiento`, `id_carrera`, `email`, `password_hash`
) VALUES (
    '8-888-888',
    'Usuario',
    NULL,
    'Estudiante',
    NULL,
    '2002-01-15',
    1,
    'user@correo.com',
    '$2y$12$aLWvg1T2M.7F/odMJrDCbeshEi9K.58z4e/FYU3kCnjHdcJIV5uhW'
);

-- Profesor: email docente@correo.com / contraseña Docente1*
INSERT INTO `biblioteca_digital`.`profesores` (
    `cip`, `primer_nombre`, `segundo_nombre`, `primer_apellido`, `segundo_apellido`,
    `fecha_nacimiento`, `email`, `departamento`, `especialidad`, `password_hash`
) VALUES (
    '9-999-999',
    'Docente',
    NULL,
    'Prueba',
    NULL,
    '1985-05-10',
    'docente@correo.com',
    'Sistemas',
    'Programación',
    '$2y$12$GIVztpjrW37FEHZLdEBAZ.5xOW9S9JNQxLZVrv8B9sBZf2bqL1qH.'
);

INSERT INTO `biblioteca_digital`.`parametros` (`clave`, `valor`) VALUES
('dias_prestamo_estudiante', '3'),
('dias_prestamo_profesor', '3');

INSERT INTO `biblioteca_digital`.`libros` (
    `isbn`, `titulo`, `autor`, `editorial`, `anio_publicacion`, `id_categoria`,
    `descripcion`, `temas`, `costo`, `unidades_totales`, `unidades_disponibles`,
    `imagen_original`, `imagen_thumb`, `firma_datos`
) VALUES
('978-0-13-110362-7', 'El Lenguaje de Programación C', 'Brian W. Kernighan & Dennis M. Ritchie', 'Prentice Hall', 1988, 2, 'La referencia clásica del lenguaje C.', 'programación, lenguaje c, sistemas', 45.00, 5, 5, 'uploads/libros/orig/lenguaje_programacion_c.jpg', 'uploads/libros/thumb/lenguaje_programacion_c.jpg', NULL),
('978-607-32-0122-6', 'Álgebra Lineal con Aplicaciones', 'Stanley Grossman', 'McGraw-Hill', 2012, 4, 'Texto universitario estándar de álgebra lineal.', 'álgebra, matrices, matemática', 38.50, 3, 3, 'uploads/libros/orig/algebra_lineal_grossman.jpg', 'uploads/libros/thumb/algebra_lineal_grossman.jpg', NULL),
('978-84-9732-542-3', 'Probabilidad y Estadística para Ingeniería', 'Murray R. Spiegel', 'McGraw-Hill', 2009, 5, 'Serie Schaum. Incluye problemas resueltos.', 'estadística, probabilidad, ingeniería', 42.75, 4, 4, 'uploads/libros/orig/probabilidad_estadistica_ingenieria.jpg', 'uploads/libros/thumb/probabilidad_estadistica_ingenieria.jpg', NULL),
('978-0-596-51774-8', 'JavaScript: The Good Parts', 'Douglas Crockford', 'O''Reilly Media', 2008, 2, 'Las partes esenciales del lenguaje JavaScript.', 'javascript, programación, web', 35.00, 2, 2, 'uploads/libros/orig/javascript_good_parts.jpg', 'uploads/libros/thumb/javascript_good_parts.jpg', NULL),
('978-84-415-3927-4', 'Química General', 'Linus Pauling', 'Reverté', 2014, 1, 'Fundamentos de química para ciencias e ingeniería.', 'química, ciencias, laboratorio', 50.00, 6, 6, 'uploads/libros/orig/quimica_general_pauling.jpg', 'uploads/libros/thumb/quimica_general_pauling.jpg', NULL);

-- ============================================================
-- 14. USUARIO DE APLICACIÓN
-- En XAMPP sirve bib_app@localhost; en Docker sirve bib_app@%.
-- ============================================================

DROP USER IF EXISTS 'bib_app'@'localhost';
DROP USER IF EXISTS 'bib_app'@'%';

CREATE USER 'bib_app'@'localhost'
IDENTIFIED BY 'B!bl10t3c@_S3cur3#2025';

CREATE USER 'bib_app'@'%'
IDENTIFIED BY 'B!bl10t3c@_S3cur3#2025';

GRANT SELECT, INSERT, UPDATE, DELETE
ON `biblioteca_digital`.*
TO 'bib_app'@'localhost';

GRANT SELECT, INSERT, UPDATE, DELETE
ON `biblioteca_digital`.*
TO 'bib_app'@'%';

FLUSH PRIVILEGES;

-- ============================================================
-- 15. VERIFICACIÓN FINAL
-- ============================================================

SELECT 'BASE DE DATOS biblioteca_digital CREADA EXITOSAMENTE' AS estado;

SELECT TABLE_NAME AS tabla
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = 'biblioteca_digital'
ORDER BY TABLE_NAME;
