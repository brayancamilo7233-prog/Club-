# Sistema de gestión del Club Acerías

Interfaz web académica responsive para registrar actividades, pagos, préstamos y asociados. Está construida con HTML5, Bootstrap 5 y CSS/JavaScript propios.

## Ejecutar localmente con PHP y MySQL

1. Inicia MySQL desde Laragon y ejecuta `DB/club_acerias.sql` en HeidiSQL conectado a la base `club`. El script crea las tablas de asociados, actividades, pagos y préstamos sin modificar la tabla `usuarios`.
    Si ya habías creado la tabla `asociados` con la versión anterior, ejecuta una sola vez `DB/migracion_persistencia.sql` para agregar los campos y tablas nuevos.
2. Comprueba los datos de conexión en `Config/conexion.php`. Por defecto usa `127.0.0.1:3306`, la base `club`, el usuario `root` y contraseña vacía. Si tu usuario o contraseña son distintos, define `DB_USER` y `DB_PASSWORD` en el entorno antes de iniciar PHP; también puedes cambiar `DB_HOST` y `DB_PORT`.
3. Abre PowerShell en la carpeta del proyecto e inicia el servidor local de PHP:

   ```powershell
    & "C:\laragon\bin\php\php-8.3.36-Win32-vs16-x64\php.exe" -S 127.0.0.1:8080 -t .
   ```

4. Abre `http://127.0.0.1:8080/Vista/login.html` en Chrome (o `http://127.0.0.1:8080` para el panel).

   En VS Code también puedes pulsar `F5` («Iniciar Chrome para localhost»): inicia este mismo servidor PHP automáticamente y luego abre Chrome.

No abras la página con `file://` ni uses Live Server: PHP necesita servirse por HTTP. Mantén abierta la terminal mientras uses el sitio y pulsa `Ctrl+C` para detener el servidor.

## Estructura

```text
Club--main/
├── index.html
├── README.md
├── Assets/
│   ├── css/
│   │   ├── club.css
│   │   └── estilos.css        # Hoja de estilos del proyecto anterior
│   └── js/
│       └── app.js
└── Vista/
    ├── Dashboart.html          # Panel del proyecto anterior
    ├── actividades.html
    ├── asociados.html
    ├── login.html              # Página del proyecto anterior
    ├── pagos.html
    ├── prestamos.html
    └── registro.html           # Página del proyecto anterior
```

## Archivos de esta interfaz

- `index.html`: panel principal con tarjetas para acceder a cada formulario.
- `Vista/actividades.html`: nombre, descripción, fecha, hora, lugar y responsable.
- `Vista/pagos.html`: número de recibo, asociado, concepto, valor, fecha y método de pago.
- `Vista/prestamos.html`: código, asociado, monto, fecha de solicitud, plazo y observaciones.
- `Vista/asociados.html`: documento, nombre, correo, teléfono, dirección, fecha de ingreso y estado.
- `Assets/css/club.css`: hoja compartida con la identidad visual azul y turquesa, bordes redondeados, bloques para campos y ajustes responsive de los cuatro formularios.
- `Assets/js/app.js`: validación del navegador, envío de formularios al backend y confirmación de guardado.

Bootstrap 5 y Bootstrap Icons se cargan desde CDN, por lo que se requiere conexión a internet para obtener esos recursos. Los formularios guardan sus datos en MySQL; los módulos de pagos y préstamos requieren que exista previamente el asociado seleccionado.

## Publicar en GitHub

Sube la carpeta completa `Club--main`, incluyendo `index.html`, `README.md`, `Assets/` y `Vista/`. No subas solo los HTML: las páginas necesitan las hojas de estilo y el JavaScript. No se requieren dependencias instaladas ni `node_modules`.

Desde una terminal situada dentro de la carpeta `Club--main`, ejecuta:

```bash
git init
git add .
git commit -m "Crear interfaz de gestión del club"
git branch -M main
git remote add origin https://github.com/USUARIO/NOMBRE-DEL-REPOSITORIO.git
git push -u origin main
```

Reemplaza `USUARIO` y `NOMBRE-DEL-REPOSITORIO` por los datos de tu repositorio. Si ya existe un remoto llamado `origin`, omite `git remote add origin ...`. Para publicar con GitHub Pages, selecciona la rama `main` y la carpeta raíz (`/`) en **Settings > Pages**.
