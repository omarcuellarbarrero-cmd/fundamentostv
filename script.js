console.log('=== INICIO script.js ===');

try {
    if (!localStorage.getItem('usuario')) {
        console.log('No hay usuario, redirigiendo a login');
        window.location.href = 'index.html';
    } else {
        console.log('Usuario encontrado:', localStorage.getItem('usuario'));
    }
} catch (e) {
    console.error('Error al verificar usuario:', e);
}

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CARGADO ===');
    
    var logoutBtn = document.getElementById('logoutBtn');
    var searchBtn = document.getElementById('searchBtn');
    var searchInput = document.getElementById('searchInput');
    
    console.log('logoutBtn existe:', !!logoutBtn);
    console.log('searchBtn existe:', !!searchBtn);
    console.log('searchInput existe:', !!searchInput);
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            console.log('Boton salir clickeado');
            if (confirm('¿Desea salir?')) {
                localStorage.removeItem('usuario');
                window.location.href = 'index.html';
            }
        });
        console.log('Event listener de logout configurado');
    }
    
    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', function() {
            console.log('Boton buscar clickeado');
            alert('Buscando: ' + searchInput.value);
        });
        console.log('Event listener de busqueda configurado');
    }
});

console.log('=== FIN script.js ===');