document.addEventListener('DOMContentLoaded', () => {
    const logueado = localStorage.getItem('logueado');
    if (!logueado) {
        window.location.href = 'login.html';
        return;
    }

    const email = localStorage.getItem('email') || 'Usuario';
    const rol = localStorage.getItem('rol') || 'usuario';

    const usuarioActual = document.getElementById('usuarioActual');
    if (usuarioActual) {
        usuarioActual.textContent = email;
    }

    const tarjetasAdministrador = document.getElementById('tarjetasAdministrador');
    const tarjetasAsociado = document.getElementById('tarjetasAsociado');

    if (tarjetasAdministrador) {
        tarjetasAdministrador.style.display = rol === 'admin' ? 'flex' : 'none';
    }

    if (tarjetasAsociado) {
        tarjetasAsociado.style.display = rol === 'admin' ? 'none' : 'block';
    }

    const btnCerrarSesion = document.getElementById('btnCerrarSesion');
    if (btnCerrarSesion) {
        btnCerrarSesion.addEventListener('click', () => {
            localStorage.removeItem('logueado');
            localStorage.removeItem('email');
            localStorage.removeItem('rol');
            window.location.href = 'login.html';
        });
    }
});