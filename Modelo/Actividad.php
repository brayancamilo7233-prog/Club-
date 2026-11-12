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
        $sql = "SELECT id, nombre, descripcion, fecha, TIME_FORMAT(hora, '%H:%i') AS hora, lugar, responsable
                FROM actividades
                ORDER BY fecha DESC, hora DESC";
        $consulta = $this->conexion->prepare($sql);
        $consulta->execute();
        return $consulta->fetchAll();
    }

    // Guarda una actividad. Las claves de $datos son los "name" del formulario actividades.html
    public function guardar($datos)
    {
        $sql = "INSERT INTO actividades (nombre, descripcion, fecha, hora, lugar, responsable)
                VALUES (:nombre, :descripcion, :fecha, :hora, :lugar, :responsable)";
        $consulta = $this->conexion->prepare($sql);
        return $consulta->execute([
            ':nombre' => trim($datos['nombreActividad'] ?? ''),
            ':descripcion' => trim($datos['descripcion'] ?? ''),
            ':fecha' => $datos['fecha'] ?? null,
            ':hora' => $datos['hora'] ?? null,
            ':lugar' => trim($datos['lugar'] ?? ''),
            ':responsable' => trim($datos['responsable'] ?? ''),
        ]);
    }
}
