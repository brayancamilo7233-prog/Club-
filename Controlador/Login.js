const usuarios = [
    { email: 'Admin@gmail.com', clave: '12345678', rol: 'admin' },
    { email: 'almin@gmail.com', clave: '12345678', rol: 'usuario' },
    { email: 'Usurio@gmail.com', clave: '12345678', rol: 'usuario' }
];

const loginForm = document.getElementById('login-form');

if (loginForm) {
    loginForm.addEventListener('submit', function (event) {
        event.preventDefault();

        const email = document.getElementById('email').value.trim().toLowerCase();
        const password = document.getElementById('password').value;
        const usuarioEncontrado = usuarios.find(
            usuario => usuario.email.toLowerCase() === email && usuario.clave === password
        );

        if (usuarioEncontrado) {
            localStorage.setItem('logueado', 'true');
            localStorage.setItem('email', usuarioEncontrado.email);
            localStorage.setItem('rol', usuarioEncontrado.rol);
            window.location.href = 'Dashboart.html';
            return;
        }

        window.alert('Usuario o contraseña incorrectos.');
    });
}