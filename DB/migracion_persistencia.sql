USE club;

ALTER TABLE asociados
  CHANGE COLUMN `Documento-Identidad` documento VARCHAR(30) NOT NULL,
  CHANGE COLUMN `Nombre` nombres VARCHAR(100) NOT NULL,
  CHANGE COLUMN `Apellido` apellidos VARCHAR(100) NOT NULL,
  ADD COLUMN fechaNacimiento DATE NULL,
  ADD COLUMN genero VARCHAR(20) NULL,
  ADD COLUMN direccionResidencia VARCHAR(200) NULL,
  ADD COLUMN ciudad VARCHAR(100) NULL,
  ADD COLUMN telefono VARCHAR(30) NOT NULL DEFAULT '',
  ADD COLUMN email VARCHAR(254) NULL,
  ADD COLUMN fechaIngreso DATE NULL,
  ADD COLUMN estado VARCHAR(20) NOT NULL DEFAULT 'activo',
  ADD COLUMN password_hash VARCHAR(255) NULL,
  ADD COLUMN rol VARCHAR(20) NOT NULL DEFAULT 'usuario',
  ADD UNIQUE KEY uq_asociados_email (email);

ALTER TABLE asociados
  CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

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
