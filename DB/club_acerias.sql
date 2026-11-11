USE club;

CREATE TABLE IF NOT EXISTS asociados (
    documento VARCHAR(30) NOT NULL,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fechaNacimiento DATE NOT NULL,
    genero VARCHAR(20) NOT NULL,
    direccionResidencia VARCHAR(200) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    email VARCHAR(254) NOT NULL,
    fechaIngreso DATE NULL,
    estado VARCHAR(20) NOT NULL DEFAULT 'activo',
    rol VARCHAR(20) NOT NULL DEFAULT 'usuario',
    password_hash VARCHAR(255) NULL,
    creadoEn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (documento),
    UNIQUE KEY uq_asociados_email (email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS actividades (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT NOT NULL,
    fecha DATE NOT NULL,
    hora TIME NOT NULL,
    lugar VARCHAR(200) NOT NULL,
    responsable VARCHAR(150) NOT NULL,
    creadoEn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS pagos (
    numeroRecibo VARCHAR(50) NOT NULL,
    asociadoDocumento VARCHAR(30) NOT NULL,
    concepto VARCHAR(200) NOT NULL,
    valor DECIMAL(12,2) NOT NULL,
    fechaPago DATE NOT NULL,
    metodoPago VARCHAR(30) NOT NULL,
    creadoEn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (numeroRecibo),
    KEY idx_pagos_asociado (asociadoDocumento),
    CONSTRAINT fk_pagos_asociado FOREIGN KEY (asociadoDocumento)
        REFERENCES asociados (documento) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prestamos (
    codigoPrestamo VARCHAR(50) NOT NULL,
    asociadoDocumento VARCHAR(30) NOT NULL,
    monto DECIMAL(12,2) NOT NULL,
    fechaSolicitud DATE NOT NULL,
    plazoMeses SMALLINT UNSIGNED NOT NULL,
    observaciones TEXT NULL,
    creadoEn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (codigoPrestamo),
    KEY idx_prestamos_asociado (asociadoDocumento),
    CONSTRAINT fk_prestamos_asociado FOREIGN KEY (asociadoDocumento)
        REFERENCES asociados (documento) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
