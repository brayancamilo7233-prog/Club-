<?php

require_once __DIR__ . '/../Config/conexion.php';

class Asociado
{
    private $conexion;
    private $datos;

    public function __construct($datos)
    {
        $this->conexion = (new Conexion())->conectar();
        $this->datos = $datos;
    }

    public function guardar()
    {
        $sql = 'INSERT INTO asociados
                (documento, nombres, apellidos, fechaNacimiento, genero, direccionResidencia, ciudad, telefono, email, fechaIngreso, estado)
                VALUES
                (:documento, :nombres, :apellidos, :fechaNacimiento, :genero, :direccionResidencia, :ciudad, :telefono, :email, :fechaIngreso, :estado)';

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':documento' => $this->datos['documento'],
            ':nombres' => $this->datos['nombres'],
            ':apellidos' => $this->datos['apellidos'],
            ':fechaNacimiento' => $this->datos['fechaNacimiento'],
            ':genero' => $this->datos['genero'],
            ':direccionResidencia' => $this->datos['direccionResidencia'],
            ':ciudad' => $this->datos['ciudad'],
            ':telefono' => $this->datos['telefono'],
            ':email' => $this->datos['email'],
            ':fechaIngreso' => $this->datos['fechaIngreso'],
            ':estado' => $this->datos['estado'],
        ]);
    }
}
