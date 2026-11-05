# Club Acerías

## Requisitos

- PHP 7.4 o posterior con las extensiones `PDO` y `pdo_mysql`.
- MySQL 5.7+ o MariaDB 10.3+.

## Preparar la base de datos

Importa `DB/club_acerias.sql` con phpMyAdmin o desde la terminal:

```sh
mysql -u root -p < DB/club_acerias.sql
```

La conexión usa por defecto `127.0.0.1`, base de datos `club_acerias`, usuario
`root` y contraseña vacía, configuración habitual de XAMPP local. Para otros
datos, configura las variables de entorno `DB_HOST`, `DB_NAME`, `DB_USER` y
`DB_PASS` antes de iniciar PHP. No guardes contraseñas de producción en el
código.

## Iniciar el proyecto

Desde la carpeta raíz del repositorio, inicia el servidor local de PHP:

```sh
php -S 127.0.0.1:8001
```

Abre <http://127.0.0.1:8001/Vista/registro.html>. El formulario envía los datos
como JSON a `Modelo/guardarAsociado.php`; las respuestas informan el resultado
en el mensaje accesible debajo de los botones.
