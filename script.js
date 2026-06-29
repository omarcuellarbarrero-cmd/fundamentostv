console.log('=== INICIO script.js ===');

// 1. Verificar autenticación
if (!localStorage.getItem('usuario')) {
    console.log('No hay usuario, redirigiendo a login');
    window.location.href = 'index.html';
} else {
    console.log('Usuario encontrado:', localStorage.getItem('usuario'));
}

// 2. Variables globales
var tipoTVSeleccionado = null;

// 3. Esperar a que el DOM esté listo
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
    
    // --- LECTOR DE VOZ (Versión Simple Automática) ---
    var vocesDisponibles = [];
    var vozSeleccionada = null;
    var speaking = false;
    
    function cargarVoces() {
        vocesDisponibles = window.speechSynthesis.getVoices();
        vozSeleccionada = vocesDisponibles.find(function(v) {
            return v.lang.startsWith('es');
        });
        if (vozSeleccionada) {
            console.log('✅ Voz en español detectada:', vozSeleccionada.name);
        }
    }
    
    if (window.speechSynthesis.onvoiceschanged !== undefined) {
        window.speechSynthesis.onvoiceschanged = cargarVoces;
    }
    cargarVoces();
    // -----------------------------------------------

    // Selección de tipo de TV
    tvButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            tvButtons.forEach(function(b) { b.classList.remove('active'); });
            this.classList.add('active');
            tipoTVSeleccionado = this.dataset.type;
            searchInput.focus();
        });
    });
    
    // Búsqueda
    searchBtn.addEventListener('click', function() {
        realizarBusqueda();
    });
    
    searchInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            realizarBusqueda();
        }
    });
    
    // Logout
    logoutBtn.addEventListener('click', function() {
        if (confirm('¿Está seguro que desea salir?')) {
            localStorage.removeItem('usuario');
            window.location.href = 'index.html';
        }
    });
    
    // Evento del botón "Escuchar"
    listenBtn.addEventListener('click', function() {
        if (!('speechSynthesis' in window)) {
            alert('Su navegador no soporta la lectura en voz alta');
            return;
        }
        
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
        
        if (vozSeleccionada) {
            utterance.voice = vozSeleccionada;
        }
        
        utterance.lang = 'es-ES';
        utterance.rate = 0.85;
        utterance.pitch = 1;
        utterance.volume = 1;
        
        utterance.onend = function() {
            listenBtn.textContent = ' Escuchar';
            speaking = false;
        };
        
        utterance.onerror = function(e) {
            console.error('Error en el lector de voz:', e);
            listenBtn.textContent = ' Escuchar';
            speaking = false;
        };
        
        window.speechSynthesis.speak(utterance);
        listenBtn.textContent = '️ Detener';
        speaking = true;
    });

    console.log('=== FIN DOMContentLoaded ===');
});

// 4. Función de búsqueda
async function realizarBusqueda() {
    var consulta = document.getElementById('searchInput').value.trim();
    
    if (!consulta) {
        alert('Por favor, ingrese qué desea consultar');
        return;
    }
    
    if (!tipoTVSeleccionado) {
        alert('Por favor, seleccione el tipo de TV (TRC o LCD/LED)');
        return;
    }
    
    document.getElementById('loading').classList.remove('hidden');
    document.getElementById('resultsSection').classList.add('hidden');
    
    try {
        var respuesta = await consultarGemini(consulta, tipoTVSeleccionado);
        mostrarResultados(respuesta);
    } catch (error) {
        console.error('Error:', error);
        document.getElementById('resultsContent').innerHTML = 
            '<p style="color: #e74c3c;"><strong>Error al obtener la información:</strong><br>' + 
            error.message + '</p>';
        document.getElementById('resultsSection').classList.remove('hidden');
    } finally {
        document.getElementById('loading').classList.add('hidden');
    }
}

// 5. Función para llamar a Gemini
async function consultarGemini(consulta, tipoTV) {
    var promptCompleto = SYSTEM_PROMPT + 
        '\n\nTIPO DE TV: ' + tipoTV + 
        '\nCONSULTA DEL TÉCNICO: ' + consulta + 
        '\n\nPor favor, proporciona una respuesta clara, ordenada y práctica.';
    
    var response = await fetch(CONFIG.GEMINI_API_URL + '?key=' + CONFIG.GEMINI_API_KEY, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            contents: [{ parts: [{ text: promptCompleto }] }],
            generationConfig: {
                temperature: 0.7,
                topK: 40,
                topP: 0.95,
                maxOutputTokens: 4096
            }
        })
    });
    
    if (!response.ok) {
        throw new Error('Error en la API de Gemini (código: ' + response.status + ')');
    }
    
    var data = await response.json();
    
    if (!data.candidates || !data.candidates[0].content.parts[0].text) {
        throw new Error('Respuesta vacía o inválida de Gemini');
    }
    
    var respuesta = data.candidates[0].content.parts[0].text;
    
    if (data.candidates[0].finishReason === 'MAX_TOKENS') {
        respuesta += '\n\n---\n⚠️ *Nota: La respuesta fue truncada por ser muy larga.*';
    }
    
    return respuesta;
}

// 6. Función para mostrar resultados
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