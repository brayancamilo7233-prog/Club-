<?php

require_once __DIR__ . '/../Config/conexion.php';

class Asociado {
    private $documento;
    private $nombres;
    private $apellidos;
    private $fechaNacimiento;
    private $genero;
    private $direccionResidencia;
    private $ciudad;
    private $telefono;
    private $email;
    private $fechaIngreso;
    private $estado;
    private $passwordHash; // Contraseña cifrada (null si el asociado no tiene cuenta)

    private ?PDO $conexion;

    public function __construct($datos) {
        // Los nombres coinciden con los atributos "name" de los formularios HTML
        $this->documento = trim($datos['documento'] ?? '');
        $this->nombres = trim($datos['nombres'] ?? '');
        $this->apellidos = trim($datos['apellidos'] ?? '');
        $this->fechaNacimiento = $datos['fechaNacimiento'] ?? null; // Formato YYYY-MM-DD del input date
        $this->genero = $datos['genero'] ?? '';
        $this->direccionResidencia = trim($datos['direccionResidencia'] ?? '');
        $this->ciudad = trim($datos['ciudad'] ?? '');
        $this->telefono = (string) ($datos['telefono'] ?? '');
        $this->email = strtolower(trim($datos['email'] ?? ''));
        $this->fechaIngreso = !empty($datos['fechaIngreso']) ? $datos['fechaIngreso'] : date('Y-m-d');
        $this->estado = $datos['estado'] ?? 'activo';

        // Solo se guarda contraseña si el formulario la envía (registro de cuenta)
        $password = $datos['password'] ?? '';
        $this->passwordHash = $password !== '' ? password_hash($password, PASSWORD_DEFAULT) : null;

        $this->conexion = (new Conexion())->conectar();
    }

    public function guardarAsociado() {
        $sql = "INSERT INTO asociados (documento, nombres, apellidos, fechaNacimiento, genero, direccionResidencia, ciudad, telefono, email, fechaIngreso, estado, password_hash)
                VALUES (:documento, :nombres, :apellidos, :fechaNacimiento, :genero, :direccionResidencia, :ciudad, :telefono, :email, :fechaIngreso, :estado, :password_hash)";

        $consulta = $this->conexion->prepare($sql);

        return $consulta->execute([
            ':documento' => $this->documento,
            ':nombres' => $this->nombres,
            ':apellidos' => $this->apellidos,
            ':fechaNacimiento' => $this->fechaNacimiento,
            ':genero' => $this->genero,
            ':direccionResidencia' => $this->direccionResidencia,
            ':ciudad' => $this->ciudad,
            ':telefono' => $this->telefono,
            ':email' => $this->email,
            ':fechaIngreso' => $this->fechaIngreso,
            ':estado' => $this->estado,
            ':password_hash' => $this->passwordHash,
        ]);
    }
}
