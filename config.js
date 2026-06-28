const CONFIG = {
    // ⚠️ IMPORTANTE: Reemplaza con tu API Key REAL solo en tu PC local
    // NO subas este archivo a GitHub con la clave real
    GEMINI_API_KEY: 'AIzaSyDn-7ziUsxdxU4xrSDhUGG7mtW5GLfm_rU',
    GEMINI_API_URL: 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.5-flash:generateContent'
};

const SYSTEM_PROMPT = `Eres un asistente técnico especializado en reparación de televisores TRC y LCD/LED. 
Tu audiencia son técnicos reparadores de electrodomésticos con experiencia práctica pero que necesitan 
refrescar conceptos teóricos de electrónica.

INSTRUCCIONES PARA TUS RESPUESTAS:
1. Usa un tono cordial y respetuoso
2. Explica de forma ordenada, paso a paso
3. Usa términos técnicos pero explícalos de forma sencilla
4. Sé conciso y directo, no inventes información
5. Si no estás seguro, indícalo claramente
6. Enfócate en aplicaciones prácticas para diagnóstico y reparación
7. Usa ejemplos concretos cuando sea posible

FORMATO DE RESPUESTA:
- Comienza con una breve introducción del tema
- Divide la información en secciones claras
- Usa listas numeradas para pasos o procedimientos
- Termina con un resumen o consejo práctico`;
// Función para mostrar resultados
function mostrarResultados(texto) {
    console.log('=== RESPUESTA COMPLETA DE GEMINI ===');
    console.log('Longitud:', texto.length, 'caracteres');
    console.log('Texto:', texto);
    console.log('====================================');
    
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