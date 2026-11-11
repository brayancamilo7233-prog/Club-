document.addEventListener('DOMContentLoaded', () => {
    if (localStorage.getItem('logueado') !== 'true') {
        window.location.href = 'login.html';
        return;
    }

    const nombre = localStorage.getItem('nombre') || 'Usuario';
    const rol = localStorage.getItem('rol') || 'usuario';

    const usuarioActual = document.getElementById('usuarioActual');
    const rolActual = document.getElementById('rolActual');

    if (usuarioActual) {
        usuarioActual.textContent = nombre;
    }

    if (rolActual) {
        rolActual.textContent = rol;
    }

    if (rol !== 'admin') {
        document.querySelectorAll('.solo-admin').forEach((tarjeta) => tarjeta.remove());
    }

    const btnCerrarSesion = document.getElementById('btnCerrarSesion');
    if (btnCerrarSesion) {
        btnCerrarSesion.addEventListener('click', cerrarSesion);
    }
});

async function cerrarSesion() {
    try {
        const respuesta = await fetch('../Controlador/CerrarSesion.php', {
            method: 'POST',
            credentials: 'same-origin'
        });

        const resultado = await respuesta.json().catch(() => ({}));

        if (!respuesta.ok || !resultado.success) {
            throw new Error('No se pudo cerrar la sesión en el servidor.');
        }
    } catch (error) {
        console.error('Error al cerrar sesión:', error);
    } finally {
        localStorage.clear();
        window.location.href = 'login.html';
    }
}
