<?php

class Conexion
{
    public function conectar()
    {
        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $puerto = getenv('DB_PORT') ?: '3306';
        $baseDatos = getenv('DB_NAME') ?: 'club';
        $usuario = getenv('DB_USER') ?: 'root';
        $contrasena = getenv('DB_PASSWORD');

        if ($contrasena === false) {
            $contrasena = '';
        }

        $dsn = "mysql:host={$host};port={$puerto};dbname={$baseDatos};charset=utf8mb4";

        return new PDO($dsn, $usuario, $contrasena, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
