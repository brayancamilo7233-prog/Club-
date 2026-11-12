USE club;

-- Agrega el total recaudado a la tabla de actividades (bases de datos ya creadas)
ALTER TABLE actividades
  ADD COLUMN totalRecaudado DECIMAL(12,2) NOT NULL DEFAULT 0.00 AFTER responsable;
