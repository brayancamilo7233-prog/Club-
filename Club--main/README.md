# Sistema de gestión del Club Acerías

Interfaz web académica responsive para registrar actividades, pagos, préstamos y asociados. Está construida con HTML5, Bootstrap 5 y CSS/JavaScript propios.

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
- `Assets/js/app.js`: validación del navegador, confirmación visual y limpieza del formulario.

Bootstrap 5 y Bootstrap Icons se cargan desde CDN, por lo que se requiere conexión a internet para obtener esos recursos. Los formularios son una maqueta de frontend: no envían ni guardan datos; para persistirlos haría falta conectarlos a un backend.

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
