console.log('=== INICIO script.js ===');

// Verificar autenticación
if (!localStorage.getItem('usuario')) {
    console.log('No hay usuario, redirigiendo a login');
    window.location.href = 'index.html';
} else {
    console.log('Usuario encontrado:', localStorage.getItem('usuario'));
}

// Variables globales
var tipoTVSeleccionado = null;

document.addEventListener('DOMContentLoaded', function() {
    console.log('=== DOM CARGADO ===');
    
    // Elementos del DOM
    var tvButtons = document.querySelectorAll('.tv-btn');
    var searchInput = document.getElementById('searchInput');
    var searchBtn = document.getElementById('searchBtn');
    var resultsSection = document.getElementById('resultsSection');
    var resultsContent = document.getElementById('resultsContent');
    var loading = document.getElementById('loading');
    var listenBtn = document.getElementById('listenBtn');
    var logoutBtn = document.getElementById('logoutBtn');
    
    console.log('Elementos encontrados:', {
        tvButtons: tvButtons.length,
        searchInput: !!searchInput,
        searchBtn: !!searchBtn,
        logoutBtn: !!logoutBtn
    });
    
    // Selección de tipo de TV
    tvButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            console.log('Botón TV clickeado:', this.dataset.type);
            tvButtons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            tipoTVSeleccionado = this.dataset.type;
            searchInput.focus();
        });
    });
    
    // Búsqueda
    searchBtn.addEventListener('click', function() {
        console.log('Botón buscar clickeado');
        realizarBusqueda();
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            console.log('Enter presionado');
            realizarBusqueda();
        }
    });
    
    // Logout
    logoutBtn.addEventListener('click', function() {
        console.log('Botón salir clickeado');
        if (confirm('¿Está seguro que desea salir?')) {
            localStorage.removeItem('usuario');
            window.location.href = 'index.html';
        }
    });
    
    // ============================================
    // LECTOR DE VOZ (Versión Simple y Automática)
    // ============================================
    
    var vocesDisponibles = [];
    var vozSeleccionada = null;
    var speaking = false;
    
    // Función para cargar y buscar voz en español automáticamente
    function cargarVoces() {
        vocesDisponibles = window.speechSynthesis.getVoices();
        
        // Buscar la primera voz disponible que sea en español
        vozSeleccionada = vocesDisponibles.find(function(v) {
            return v.lang.startsWith('es');
        });
        
        if (vozSeleccionada) {
            console.log('✅ Voz en español detectada:', vozSeleccionada.name);
        } else {
            console.warn('⚠️ No se encontró voz en español. Se usará la predeterminada.');
        }
    }
    
    // En algunos navegadores (como Chrome), las voces se cargan de forma asíncrona
    if (window.speechSynthesis.onvoiceschanged !== undefined) {
        window.speechSynthesis.onvoiceschanged = cargarVoces;
    }
    cargarVoces(); // Intento inicial
    
    // Evento del botón "Escuchar"
    listenBtn.addEventListener('click', function() {
        if (!('speechSynthesis' in window)) {
            alert('Su navegador no soporta la lectura en voz alta');
            return;
        }
        
        // Si ya está hablando, detener
        if (speaking) {
            window.speechSynthesis.cancel();
            listenBtn.textContent = '🔊 Escuchar';
            speaking = false;
            return;
        }
        
        var texto = resultsContent.innerText;
        if (!texto.trim()) {
            alert('No hay información para leer');
            return;
        }
        
        var utterance = new SpeechSynthesisUtterance(texto);
        
        // Asignar la voz en español si el navegador la tiene
        if (vozSeleccionada) {
            utterance.voice = vozSeleccionada;
        }
        
        // Configuración optimizada para adultos mayores (más pausada y clara)
        utterance.lang = 'es-ES';
        utterance.rate = 0.85;    // Velocidad un 15% más lenta
        utterance.pitch = 1;      // Tono normal
        utterance.volume = 1;     // Volumen al máximo
        
        // Cuando termine de leer
        utterance.onend = function() {
            listenBtn.textContent = '🔊 Escuchar';
            speaking = false;
        };
        
        // Si hay algún error
        utterance.onerror = function(e) {
            console.error('Error en el lector de voz:', e);
            listenBtn.textContent = '🔊 Escuchar';
            speaking = false;
        };
        
        // Iniciar lectura
        window.speechSynthesis.speak(utterance);
        listenBtn.textContent = '⏸️ Detener';
        speaking = true;
    }
);

// Función de búsqueda
async function realizarBusqueda() {
    console.log('Iniciando búsqueda...');
    
    var consulta = document.getElementById('searchInput').value.trim();
    
    if (!consulta) {
        alert('Por favor, ingrese qué desea consultar');
        return;
    }
    
    if (!tipoTVSeleccionado) {
        alert('Por favor, seleccione el tipo de TV (TRC o LCD/LED)');
        return;
    }
    
    console.log('Consulta:', consulta, 'Tipo TV:', tipoTVSeleccionado);
    
    // Mostrar loading
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('resultsSection').classList.add('hidden');
    
    try {
        var respuesta = await consultarGemini(consulta, tipoTVSeleccionado);
        mostrarResultados(respuesta);
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('resultsContent').innerHTML = 
            '<p style="color: #e74c3c;"><strong>Error al obtener la información:</strong><br>' + 
            error.message + '</p><p>Verifique su conexión a internet o contacte al administrador.</p>';
        document.getElementById('resultsSection').classList.remove('hidden');
    } finally {
        document.getElementById('loading').classList.add('hidden');
    }
}

// Función para llamar a Gemini
async function consultarGemini(consulta, tipoTV) {
    var promptCompleto = SYSTEM_PROMPT + 
        '\n\nTIPO DE TV: ' + tipoTV + 
        '\nCONSULTA DEL TÉCNICO: ' + consulta + 
        '\n\nPor favor, proporciona una respuesta clara, ordenada y práctica para el técnico reparador.';
    
    var response = await fetch(CONFIG.GEMINI_API_URL + '?key=' + CONFIG.GEMINI_API_KEY, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            contents: [{
                parts: [{
                    text: promptCompleto
                }]
            }],
            generationConfig: {
                temperature: 0.7,
                topK: 40,
                topP: 0.95,
                maxOutputTokens: 4096  // 👈 AUMENTADO de 1024 a 4096
            }
        })
    });
    
    if (!response.ok) {
        throw new Error('Error en la API de Gemini (código: ' + response.status + ')');
    }
    
    var data = await response.json();
    return data.candidates[0].content.parts[0].text;
}

// Función para mostrar resultados
function mostrarResultados(texto) {
    var html = texto
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/^### (.*$)/gm, '<h4>$1</h4>')
        .replace(/^## (.*$)/gm, '<h4>$1</h4>')
        .replace(/^# (.*$)/gm, '<h4>$1</h4>')
        .replace(/^\- (.*$)/gm, '<li>$1</li>')
        .replace(/^\* (.*$)/gm, '<li>$1</li>')
        .replace(/^\d+\. (.*$)/gm, '<li>$1</li>')
        .replace(/<\/li>\n<li>/g, '</li><li>')
        .replace(/\n\n/g, '</p><p>')
        .replace(/\n/g, '<br>');
    
    html = html.replace(/(<li>.*?<\/li>)/gs, '<ul>$1</ul>');
    html = html.replace(/<\/ul><ul>/g, '');
    html = '<p>' + html + '</p>';
    html = html.replace(/<p><\/p>/g, '');
    html = html.replace(/<p><ul>/g, '<ul>');
    html = html.replace(/<\/ul><\/p>/g, '</ul>');
    html = html.replace(/<p><h4>/g, '<h4>');
    html = html.replace(/<\/h4><\/p>/g, '</h4>');
    
    document.getElementById('resultsContent').innerHTML = html;
    document.getElementById('resultsSection').classList.remove('hidden');
    document.getElementById('resultsSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

console.log('=== FIN script.js ===');