CREATE DATABASE IF NOT EXISTS club_acerias
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE club_acerias;

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
    creadoEn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (documento),
    UNIQUE KEY uq_asociados_email (email)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;
