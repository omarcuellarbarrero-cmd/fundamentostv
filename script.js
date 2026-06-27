async function consultarGemini(consulta, tipoTV) {
    const promptCompleto = `
        ${SYSTEM_PROMPT}
        
        TIPO DE TV: ${tipoTV}
        CONSULTA DEL TÉCNICO: ${consulta}
        
        Por favor, proporciona una respuesta clara, ordenada y práctica para el técnico reparador.
    `;
    
    const response = await fetch(`${CONFIG.GEMINI_API_URL}?key=${CONFIG.GEMINI_API_KEY}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
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
                maxOutputTokens: 1024,
            }
        })
    });
    
    if (!response.ok) {
        throw new Error('Error en la API de Gemini');
    }
    
    const data = await response.json();
    return data.candidates[0].content.parts[0].text;
}