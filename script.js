document.addEventListener('DOMContentLoaded', function() {
    // ... (mantener el código existente del login) ...

    const loginForm = document.querySelector('#loginSection form');
    const errorMessage = document.querySelector('#errorMessage');
    const dashboardSection = document.querySelector('#dashboardSection');
    const loginSection = document.querySelector('#loginSection');
    const userNameSpan = document.querySelector('#userName');

    // Compruebe si el usuario ya ha iniciado sesión
    checkSession();
    // Update the login form submit handler
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const email = document.querySelector('#email').value;
        const password = document.querySelector('#password').value;

        try {
            const response = await fetch('/php/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email, password })
            });

            const data = await response.json();

            if (data.success) {
                // Redirect to the appropriate dashboard
                window.location.href = data.redirect;
            } else {
                errorMessage.textContent = data.error;
                errorMessage.style.display = 'block';
            }
        } catch (error) {
            errorMessage.textContent = 'An error occurred. Please try again.';
            errorMessage.style.display = 'block';
        }
    });

    // Función modificada para checkSession
    async function checkSession() {
        try {
            const response = await fetch('php/get_user_info.php');
            const data = await response.json();

            if (data.success) {
                // Obtener el nombre del archivo actual
                const currentPage = window.location.pathname.split('/').pop();
                
                // Determinar la página correcta según el tipo de usuario
                let correctPage = '';
                switch (data.user.tipo_usuario) {
                    case 'student':
                        correctPage = 'estudiante.html';
                        break;
                    case 'teacher':
                        correctPage = 'profesor.html';
                        break;
                    case 'admin':
                        correctPage = 'admin.html';
                        break;
                    case 'director':
                        correctPage = 'director.html';
                        break;
                    default:
                        correctPage = 'index.html';
                }

                // Solo redirigir si el usuario está en la página incorrecta
                if (currentPage === 'index.html' && currentPage !== correctPage) {
                    window.location.href = correctPage;
                } else if (currentPage === correctPage) {
                    // Si está en la página correcta, mostrar el dashboard
                    loginSection.style.display = 'none';
                    dashboardSection.style.display = 'block';
                    updateUserInfo(data.user);
                }
            } else {
                // Si no hay sesión y no estamos en index.html, redirigir al login
                const currentPage = window.location.pathname.split('/').pop();
                if (currentPage !== 'index.html') {
                    window.location.href = 'index.html';
                } else {
                    loginSection.style.display = 'block';
                    dashboardSection.style.display = 'none';
                }
            }
        } catch (error) {
            console.error('Error checking session:', error);
            // Solo mostrar login si estamos en index.html
            if (window.location.pathname.endsWith('index.html')) {
                loginSection.style.display = 'block';
                dashboardSection.style.display = 'none';
            }
        }
    }

    // Función para actualizar la información del usuario en el dashboard
    function updateUserInfo(user) {
        if (!user) return;
        
        userNameSpan.textContent = `${user.nombre} ${user.apellido_paterno}`;
        // Actualizar otros campos del usuario si existen
        const fields = {
            'id': user.id,
            'tipo_usuario': user.tipo_usuario,
            'nombre': `${user.nombre} ${user.apellido_paterno} ${user.apellido_materno}`,
            'ic': user.ic,
            'email': user.email,
            'sexo': user.sexo,
            'telefono': user.telefono,
            'fecha_registro': new Date(user.fecha_registro).toLocaleString()
        };

        for (const [field, value] of Object.entries(fields)) {
            const element = document.querySelector(`[data-field="${field}"]`);
            if (element) element.textContent = value;
        }
    }
    

    function showDashboard(user) {
        loginSection.style.display = 'none'; // Corregido: "ninguno" a "none"
        dashboardSection.style.display = 'block'; // Corregido: "bloque" a "block"
        
        // Actualizar la información del usuario
        userNameSpan.textContent = `${user.nombre} ${user.apellido_paterno}`; // Corregido: faltaba la comilla
        document.querySelector('[data-field="id"]').textContent = user.id;
        document.querySelector('[data-field="tipo_usuario"]').textContent = user.tipo_usuario;
        document.querySelector('[data-field="nombre"]').textContent = 
            `${user.nombre} ${user.apellido_paterno} ${user.apellido_materno}`; // Corregido: faltaban las comillas
        document.querySelector('[data-field="ic"]').textContent = user.ic;
        document.querySelector('[data-field="email"]').textContent = user.email;
        document.querySelector('[data-field="sexo"]').textContent = user.sexo;
        document.querySelector('[data-field="telefono"]').textContent = user.telefono;
        document.querySelector('[data-field="fecha_registro"]').textContent = 
            new Date(user.fecha_registro).toLocaleString(); // Corregido: "nueva Fecha" a "new Date"
    }

    // Registro de usuario
    const registerForm = document.querySelector('#registerForm');
    const registerError = document.querySelector('#registerError');
    const registerSuccess = document.querySelector('#registerSuccess');

    registerForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = new FormData(registerForm);
        
        try {
            const response = await fetch('php/registro.php', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                registerSuccess.textContent = data.message; // Corregido: "registersuccess" a "registerSuccess"
                registerSuccess.style.display = 'block'; // Corregido: "bloque" a "block"
                registerError.style.display = 'none'; // Corregido: "ninguno" a "none"
                registerForm.reset();
                
                // Opcional: redirigir al iniciar sesión después de un tiempo
                setTimeout(() => {
                    showLoginForm();
                }, 3000);
            } else {
                registerError.textContent = data.error;
                registerError.style.display = 'block'; // Corregido: "bloque" a "block"
                registerSuccess.style.display = 'none'; // Corregido: "ninguno" a "none"
            }
        } catch (error) {
            registerError.textContent = 'Error en el registro. Por favor, inténtalo de nuevo.'; // Corregido: faltaba cerrar la comilla
            registerError.style.display = 'block'; // Corregido: "bloque" a "block"
            registerSuccess.style.display = 'none'; // Corregido: "ninguno" a "none"
        }
    });
}); // Funciones para mostrar/ocultar formularios

function showRegisterForm() {
    document.getElementById('loginSection').style.display = 'none'; // Corregido: "ninguno" a "none"
    document.getElementById('registerSection').style.display = 'block'; // Corregido: "bloque" a "block"
    document.getElementById('dashboardSection').style.display = 'none'; // Corregido: "ninguno" a "none"
}

function showLoginForm() {
    document.getElementById('loginSection').style.display = 'block'; // Corregido: "bloque" a "block"
    document.getElementById('registerSection').style.display = 'none'; // Corregido: "ninguno" a "none"
    document.getElementById('dashboardSection').style.display = 'none'; // Corregido: "ninguno" a "none"
}
