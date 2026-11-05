<?php

class Conexion
{
    public function conectar()
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $baseDatos = getenv('DB_NAME') ?: 'club_acerias';
        $usuario = getenv('DB_USER') ?: 'root';
        $contrasena = getenv('DB_PASS');

        if ($contrasena === false) {
            $contrasena = '';
        }

        $dsn = "mysql:host={$host};dbname={$baseDatos};charset=utf8mb4";

        return new PDO($dsn, $usuario, $contrasena, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    }
}
