<?php

require_once __DIR__ . '/../Config/conexion.php';

class Actividad
{
    private $conexion;

    public function __construct()
    {
        $conexionDB = new Conexion();
        $this->conexion = $conexionDB->conectar();
    }

    // Devuelve todas las actividades, de la más reciente a la más antigua
    public function obtenerActividades()
    {
        $sql = "SELECT id, nombre, descripcion, fecha, TIME_FORMAT(hora, '%H:%i') AS hora, lugar, responsable, totalRecaudado
                FROM actividades
                ORDER BY fecha DESC, hora DESC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll();
    }

    // Lee "totalRecaudado" de $datos: vacío o ausente = 0. Devuelve null si no es un número válido (>= 0)
    private function leerTotalRecaudado($datos)
    {
        $valor = $datos['totalRecaudado'] ?? '';
        if ($valor === '' || $valor === null) {
            return 0;
        }
        if (!is_numeric($valor) || (float) $valor < 0) {
            return null;
        }
        return round((float) $valor, 2);
    }

    // Guarda una actividad. Las claves de $datos son los "name" del formulario actividades.html
    // "totalRecaudado" es opcional (por defecto 0)
    public function guardar($datos)
    {
        $total = $this->leerTotalRecaudado($datos);
        if ($total === null) {
            return false;
        }

        $sql = "INSERT INTO actividades (nombre, descripcion, fecha, hora, lugar, responsable, totalRecaudado)
                VALUES (:nombre, :descripcion, :fecha, :hora, :lugar, :responsable, :totalRecaudado)";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            ':nombre' => trim($datos['nombreActividad'] ?? ''),
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':fecha' => $datos['fecha'] ?? null,
            ':hora' => $datos['hora'] ?? null,
            ':lugar' => trim($datos['lugar'] ?? ''),
            ':responsable' => trim($datos['responsable'] ?? ''),
            ':totalRecaudado' => $total,
        ]);
    }

    // Elimina una actividad por su id. Devuelve true solo si realmente se borró una fila
    public function eliminar($idActividad)
    {
        $id = filter_var($idActividad, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if ($id === false) {
            return false;
        }

        $sql = "DELETE FROM actividades WHERE id = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([':id' => $id]);
        return $consulta->rowCount() > 0;
    }

    // Actualiza una actividad existente. $datos usa los mismos nombres que guardar() más "idActividad"
    // Devuelve true si la actividad existe (aunque los datos no hayan cambiado) y false si no existe
    public function editar($datos)
    {
        $id = filter_var($datos['idActividad'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $total = $this->leerTotalRecaudado($datos);
        if ($id === false || $total === null) {
            return false;
        }

        $sql = "UPDATE actividades
                SET nombre = :nombre,
                    descripcion = :descripcion,
                    fecha = :fecha,
                    hora = :hora,
                    lugar = :lugar,
                    responsable = :responsable,
                    totalRecaudado = :totalRecaudado
                WHERE id = :id";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute([
            ':nombre' => trim($datos['nombreActividad'] ?? ''),
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':fecha' => $datos['fecha'] ?? null,
            ':hora' => $datos['hora'] ?? null,
            ':lugar' => trim($datos['lugar'] ?? ''),
            ':responsable' => trim($datos['responsable'] ?? ''),
            ':totalRecaudado' => $total,
            ':id' => $id,
        ]);

        if ($consulta->rowCount() > 0) {
            return true;
        }

        // MySQL devuelve 0 filas afectadas si los datos son iguales: comprobar que el id existe
        $existe = $this->conexion->prepare("SELECT 1 FROM actividades WHERE id = :id");
        $existe->execute([':id' => $id]);
        return (bool) $existe->fetchColumn();
    }
}
