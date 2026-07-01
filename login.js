// ============================================
// 🔐 LÓGICA DE LOGIN
// ============================================
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const user = document.getElementById('username').value.trim();
    const pass = document.getElementById('password').value.trim();
    
    // Credenciales (puedes cambiarlas aquí)
    const usuarios = {
        'tecnico1': 'clave123',
        'reparador': 'tv2024',
        'admin': 'admin2026'
    };
    
    if (usuarios[user] && usuarios[user] === pass) {
        sessionStorage.setItem('userLoggedIn', 'true');
        sessionStorage.setItem('currentUser', user);
        window.location.href = 'app.html';
    } else {
        alert('❌ Usuario o contraseña incorrectos');
    }
});
