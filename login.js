// ============================================
// 🔐 LÓGICA DE LOGIN
// ============================================
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const user = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value.trim();
    
    // Credenciales (puedes cambiarlas aquí)
    const VALID_USER = 'admin';
    const VALID_PASS = 'admin2026';
    
    if (user === VALID_USER && pass === VALID_PASS) {
        sessionStorage.setItem('userLoggedIn', 'true');
        window.location.href = 'app.html';
    } else {
        alert('❌ Usuario o contraseña incorrectos');
    }
});