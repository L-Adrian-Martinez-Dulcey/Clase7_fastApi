document.addEventListener('DOMContentLoaded', () => {
    const loginForm = document.getElementById('loginForm');
    const loginMessage = document.getElementById('loginMessage');
    const logoutBtn = document.getElementById('logoutBtn');
    const userName = document.getElementById('userName');

    const setUserDisplay = (user) => {
        if (userName && user && user.name) {
            userName.textContent = user.name;
        }
    };

    const storedUser = JSON.parse(sessionStorage.getItem('evoria_user') || 'null');
    if (storedUser) {
        setUserDisplay(storedUser);
    }

    if (loginForm) {
        loginForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;

            if (!email || !password) {
                loginMessage.textContent = 'Por favor completa todos los campos.';
                return;
            }

            // Mostrar mensaje de carga
            loginMessage.textContent = 'Iniciando sesión...';
            loginMessage.style.color = '#2563eb';

            try {
                const response = await fetch('/Evoria/backend/routes/api.php?route=login', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        email: email,
                        password: password
                    })
                });

                const result = await response.json();
                
                if (response.ok && result.success) {
                    sessionStorage.setItem('evoria_user', JSON.stringify(result.user));
                    loginMessage.textContent = 'Inicio de sesión exitoso. Redirigiendo...';
                    loginMessage.style.color = '#16a34a';
                    setTimeout(() => {
                        window.location.href = 'index.php?page=dashboard';
                    }, 1000);
                } else {
                    loginMessage.textContent = result.message || 'Error al iniciar sesión.';
                    loginMessage.style.color = '#dc2626';
                }
            } catch (error) {
                console.error('Error:', error);
                loginMessage.textContent = 'Error de conexión: ' + error.message;
                loginMessage.style.color = '#dc2626';
            }
        });
    }

    if (logoutBtn) {
        logoutBtn.addEventListener('click', async () => {
            try {
                const response = await fetch('/Evoria/backend/routes/api.php?route=logout', {
                    method: 'POST'
                });
                const result = await response.json();
                if (response.ok && result.success) {
                    sessionStorage.removeItem('evoria_user');
                    window.location.href = 'index.php';
                }
            } catch (error) {
                sessionStorage.removeItem('evoria_user');
                window.location.href = 'index.php';
            }
        });
    }
});
