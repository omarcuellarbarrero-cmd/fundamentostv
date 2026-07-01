// ============================================
// 🔒 PROTECCIÓN: Verificar sesión activa
// ============================================
if (sessionStorage.getItem('userLoggedIn') !== 'true') {
    window.location.href = 'index.html';
}

// ============================================
// 🎯 VARIABLES GLOBALES
// ============================================
let selectedTVType = null;

// ============================================
// 📺 SELECCIÓN DE TIPO DE TV
// ============================================
document.querySelectorAll('.btn-tv-type').forEach(function(button) {
    button.addEventListener('click', function() {
        document.querySelectorAll('.btn-tv-type').forEach(btn => btn.classList.remove('active'));
        this.classList.add('active');
        selectedTVType = this.dataset.type;
        document.getElementById('tvTypeSelected').textContent = '✅ Seleccionó: ' + selectedTVType;
    });
});

// ============================================
// 📤 ENVÍO DEL FORMULARIO DE BÚSQUEDA
// ============================================
// 🔍 LOGS DE DEPURACIÓN
console.log('🔍 app.js cargado correctamente');
console.log('🔍 Formulario searchForm:', document.getElementById('searchForm') ? '✅ Encontrado' : '❌ NO encontrado');
console.log('🔍 Botones TV type:', document.querySelectorAll('.btn-tv-type').length);
document.getElementById('searchForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    if (!selectedTVType) {
        alert('⚠️ Por favor, primero seleccione el tipo de televisor (TRC o LCD/LED)');
        return;
    }
    
    const term = document.getElementById('searchTerm').value.trim();
    if (!term) {
        alert('⚠️ Por favor, ingrese un término o concepto.');
        return;
    }
    
    const resultSection = document.getElementById('resultSection');
    const resultContent = document.getElementById('resultContent');
    
    // Mostrar sección y estado de carga
    resultSection.style.display = 'block';
    resultContent.innerHTML = '<p>🎓 Preparando explicación didáctica...</p>';
    resultSection.scrollIntoView({ behavior: 'smooth' });
    
    try {
        const prompt = buildTutorPrompt(selectedTVType, term);
        const response = await callGroqAPI(prompt);
        resultContent.innerHTML = formatResponse(response);
    } catch (error) {
        console.error('Error:', error);
        resultContent.innerHTML = '<p style="color:red;">❌ Error: ' + error.message + '</p>';
    }
});

// ============================================
// 📝 CONSTRUIR PROMPT DE TUTOR VIRTUAL
// ============================================
function buildTutorPrompt(tvType, term) {
    return 'Eres un Tutor Virtual experto en electrónica de televisores. ' +
        'Un estudiante o técnico necesita aprender sobre un concepto específico.\n\n' +
        'CONTEXTO:\n' +
        '- Tipo de TV: ' + tvType + '\n' +
        '- Término o concepto a explicar: ' + term + '\n\n' +
        'INSTRUCCIONES PARA TU EXPLICACIÓN:\n' +
        '1. Comienza con una definición clara y sencilla (2-3 oraciones).\n' +
        '2. Explica cuál es su función principal en el televisor.\n' +
        '3. Usa una analogía simple para que sea fácil de entender (ej: "es como...").\n' +
        '4. Describe dónde se ubica físicamente o cómo identificarlo.\n' +
        '5. Menciona 2 o 3 fallas comunes relacionadas con este componente/concepto.\n' +
        '6. Da un consejo práctico o tip de seguridad/mantenimiento.\n' +
        '7. Usa un tono didáctico, claro y en español.\n' +
        '8. Organiza la respuesta con subtítulos y listas.\n' +
        '9. Sé conciso pero completo (máximo 400 palabras).\n' +
        '10. Al final, agrega una sección llamada "💡 Tip del Experto" con un dato curioso o práctico.';
}

// ============================================
//  LLAMAR AL PROXY PHP (api.php)
// ============================================
async function callGroqAPI(prompt) {
    const response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ prompt: prompt })
    });
    
    if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Error del servidor');
    }
    
    const data = await response.json();
    return data.text;
}

// ============================================
// 📄 FORMATEAR RESPUESTA EN HTML
// ============================================
function formatResponse(text) {
    let html = '';
    const lines = text.split('\n');
    let inOl = false, inUl = false;
    
    for (let line of lines) {
        line = line.trim();
        if (!line) {
            if (inOl) { html += '</ol>'; inOl = false; }
            if (inUl) { html += '</ul>'; inUl = false; }
            continue;
        }
        
        if (line.startsWith('### ') || line.startsWith('## ') || line.startsWith('# ')) {
            if (inOl) { html += '</ol>'; inOl = false; }
            if (inUl) { html += '</ul>'; inUl = false; }
            html += '<h3>' + line.replace(/^#+\s/, '') + '</h3>';
        } else if (/^\d+\.\s/.test(line)) {
            if (inUl) { html += '</ul>'; inUl = false; }
            if (!inOl) { html += '<ol>'; inOl = true; }
            html += '<li>' + line.replace(/^\d+\.\s/, '') + '</li>';
        } else if (/^[-*•]\s/.test(line)) {
            if (inOl) { html += '</ol>'; inOl = false; }
            if (!inUl) { html += '<ul>'; inUl = true; }
            html += '<li>' + line.replace(/^[-*•]\s/, '') + '</li>';
        } else {
            if (inOl) { html += '</ol>'; inOl = false; }
            if (inUl) { html += '</ul>'; inUl = false; }
            html += '<p>' + line + '</p>';
        }
    }
    if (inOl) html += '</ol>';
    if (inUl) html += '</ul>';
    return html;
}

// ============================================
// 🚪 CERRAR SESIÓN
// ============================================
document.getElementById('logoutBtn').addEventListener('click', function() {
    if (confirm('¿Está seguro que desea salir?')) {
        sessionStorage.removeItem('userLoggedIn');
        window.location.href = 'index.html';
    }
});

// ============================================
// 🔊 LECTOR DE VOZ
// ============================================
let isSpeaking = false;
let spanishVoice = null;
const btnVoice = document.getElementById('btnVoice');

function loadSpanishVoice() {
    const voices = speechSynthesis.getVoices();
    spanishVoice = voices.find(v => v.lang.startsWith('es-MX')) ||
                   voices.find(v => v.lang.startsWith('es-ES')) ||
                   voices.find(v => v.lang.startsWith('es')) || voices[0];
}
if (speechSynthesis.onvoiceschanged !== undefined) {
    speechSynthesis.onvoiceschanged = loadSpanishVoice;
}
setTimeout(loadSpanishVoice, 500);

btnVoice.addEventListener('click', function() {
    const text = document.getElementById('resultContent').innerText;
    if (!text) return;

    if (!isSpeaking) {
        const utterance = new SpeechSynthesisUtterance(text);
        if (spanishVoice) utterance.voice = spanishVoice;
        utterance.lang = 'es-ES';
        utterance.onend = () => { isSpeaking = false; btnVoice.textContent = '🔊 Escuchar'; };
        speechSynthesis.speak(utterance);
        isSpeaking = true;
        btnVoice.textContent = '⏹️ Detener';
    } else {
        speechSynthesis.cancel();
        isSpeaking = false;
        btnVoice.textContent = '🔊 Escuchar';
    }
});
