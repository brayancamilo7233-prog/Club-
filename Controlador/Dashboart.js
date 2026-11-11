document.addEventListener('DOMContentLoaded', () => {
    // Si no ha iniciado sesión, volver al login
    if (localStorage.getItem('logueado') !== 'true') {
        window.location.href = 'login.html';
        return;
    }

    // Datos que guardó Login.js con la respuesta de IniciarSesion.php
    const nombre = localStorage.getItem('nombre') || 'Usuario';
    const rol = localStorage.getItem('rol') || 'usuario';

    document.getElementById('usuarioActual').textContent = nombre;
    document.getElementById('rolActual').textContent = rol;

    // Las tarjetas marcadas como "solo-admin" se ocultan a los usuarios normales
    if (rol !== 'admin') {
        document.querySelectorAll('.solo-admin').forEach((tarjeta) => tarjeta.remove());
    }

    document.getElementById('btnCerrarSesion').addEventListener('click', () => {
        localStorage.removeItem('logueado');
        localStorage.removeItem('nombre');
        localStorage.removeItem('rol');
        window.location.href = 'login.html';
    });
});
