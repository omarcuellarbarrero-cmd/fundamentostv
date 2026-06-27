<?php
// ============================================
// PROXY BACKEND PARA GEMINI API
// Protege tu API Key del frontend
// ============================================

// 1. CONFIGURACIÓN - ¡AQUÍ VA TU API KEY!
define('GEMINI_API_KEY', 'AQ.Ab8RN6IIz00H-j556S5vBFdt7IeZUMuR_Xcu59g-CL_48Wljng');
define('GEMINI_MODEL', 'gemini-3.5-flash');
define('MAX_REQUESTS_PER_MINUTE', 20);

// 2. CABECERAS CORS - Solo permitir tu dominio
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: https://asistente.omarcuellar.co');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Manejar preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 3. VALIDAR QUE SEA PETICIÓN POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// 4. RATE LIMITING SIMPLE (por IP)
function checkRateLimit($ip, $maxRequests, $windowSeconds) {
    $file = sys_get_temp_dir() . '/rate_limit_' . md5($ip);
    $now = time();
    $data = [];
    
    if (file_exists($file)) {
        $data = json_decode(file_get_contents($file), true) ?: [];
    }
    
    $data = array_filter($data, function($timestamp) use ($now, $windowSeconds) {
        return ($now - $timestamp) < $windowSeconds;
    });
    
    if (count($data) >= $maxRequests) {
        return false;
    }
    
    $data[] = $now;
    file_put_contents($file, json_encode($data));
    return true;
}

$clientIp = $_SERVER['REMOTE_ADDR'];
if (!checkRateLimit($clientIp, MAX_REQUESTS_PER_MINUTE, 60)) {
    http_response_code(429);
    echo json_encode(['error' => 'Demasiadas solicitudes. Espera un momento.']);
    exit;
}

// 5. LEER Y VALIDAR DATOS DEL FRONTEND
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['consulta']) || !isset($input['tipoTV'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Datos incompletos']);
    exit;
}

$consulta = trim($input['consulta']);
$tipoTV = trim($input['tipoTV']);

if (strlen($consulta) < 3 || strlen($consulta) > 500) {
    http_response_code(400);
    echo json_encode(['error' => 'La consulta debe tener entre 3 y 500 caracteres']);
    exit;
}

if (!in_array($tipoTV, ['TRC', 'LCD'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Tipo de TV inválido']);
    exit;
}

// 6. CONSTRUIR PROMPT PARA GEMINI
$systemPrompt = <<<PROMPT
Eres un asistente técnico especializado en reparación de televisores TRC y LCD/LED.
Tu audiencia son técnicos reparadores de electrodomésticos con experiencia práctica.

INSTRUCCIONES:
1. Tono cordial y respetuoso
2. Explica paso a paso, de forma ordenada
3. Usa términos técnicos pero explícalos sencillamente
4. Sé conciso, no inventes información
5. Si no estás seguro, indícalo
6. Enfócate en diagnóstico y reparación práctica

FORMATO:
- Introducción breve
- Secciones claras
- Listas numeradas para procedimientos
- Resumen o consejo práctico al final
PROMPT;

$promptCompleto = "$systemPrompt\n\nTIPO DE TV: $tipoTV\nCONSULTA: $consulta";

// 7. LLAMAR A GEMINI API
$geminiUrl = "https://generativelanguage.googleapis.com/v1beta/models/" . GEMINI_MODEL . ":generateContent?key=" . GEMINI_API_KEY;

$payload = [
    'contents' => [[
        'parts' => [['text' => $promptCompleto]]
    ]],
    'generationConfig' => [
        'temperature' => 0.7,
        'topK' => 40,
        'topP' => 0.95,
        'maxOutputTokens' => 1024
    ]
];

$ch = curl_init($geminiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Error de conexión con Gemini']);
    exit;
}

if ($httpCode !== 200) {
    http_response_code(502);
    echo json_encode(['error' => 'Error en el servicio de IA']);
    exit;
}

$data = json_decode($response, true);

if (!isset($data['candidates'][0]['content']['parts'][0]['text'])) {
    http_response_code(500);
    echo json_encode(['error' => 'Respuesta inválida de Gemini']);
    exit;
}

// 8. DEVOLVER RESPUESTA AL FRONTEND
echo json_encode([
    'success' => true,
    'respuesta' => $data['candidates'][0]['content']['parts'][0]['text']
]);