<?php
// Configurar sesión antes de iniciar
if (session_status() === PHP_SESSION_NONE) {
    // Guardar sesiones en la carpeta data (persistente en Docker)
    session_save_path(__DIR__ . '/data/sessions');
    
    // Crear la carpeta si no existe
    if (!is_dir(__DIR__ . '/data/sessions')) {
        mkdir(__DIR__ . '/data/sessions', 0777, true);
    }
    
    // Configurar cookies para HTTPS
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',  // Dejar vacío para que use el dominio actual
        'secure' => true,  // Importante para HTTPS
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// 🔒 SEGURIDAD: Si no hay sesión, fuera.
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$username = htmlspecialchars($_SESSION['username'] ?? 'Usuario');
$is_admin = !empty($_SESSION['is_admin']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fundamentos TV - App</title>
    
    <!-- Tus estilos originales -->
    <link rel="stylesheet" href="styles.css">
    
    <!-- Estilos adicionales para la sesión (Barra superior) -->
    <style>
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 1000;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 15px;
            border-radius: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .user-name {
            color: #2c3e50;
            font-weight: 600;
            font-size: 14px;
        }
        .btn-logout-top {
            background: #e74c3c;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: background 0.3s;
        }
        .btn-logout-top:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Barra de información del usuario (Siempre visible) -->
    <div class="user-info">
        <span class="user-name">👋 Hola, <?= $username ?></span>
        <a href="logout.php" class="btn-logout-top">🚪 Salir</a>
    </div>

    <div class="container">
        <header class="app-header">
            <div class="logo-container">
                <img src="Logo-nuevo-metodo-oc.webp" alt="Logo" class="logo-main">
            </div>
            <h1>🔧 Fundamentación Técnica</h1>
            <p class="subtitle">Asistente Técnico Virtual</p>
            <!-- NOTA: Se eliminó el botón "Salir" antiguo para evitar conflictos con JS -->
        </header>

        <main class="app-main">
            <!-- Selección de tipo de TV -->
            <section class="card">
                <h2>📺 Seleccione el tipo de TV</h2>
                <div class="tv-type-selector">
                    <button type="button" class="btn-tv-type" data-type="TRC">
                        📺 TV TRC<br><small>(Tubo de rayos catódicos)</small>
                    </button>
                    <button type="button" class="btn-tv-type" data-type="LCD/LED">
                        🖥️ TV LCD/LED<br><small>(Pantalla plana)</small>
                    </button>
                </div>
                <p id="tvTypeSelected" class="selection-indicator"></p>
            </section>

            <!-- Formulario de búsqueda -->
            <section class="card">
                <h2>🔍 Buscar Concepto</h2>
                <form id="searchForm">
                    <div class="form-group">
                        <label for="searchTerm">¿Qué necesita saber o recordar?</label>
                        <input type="text" id="searchTerm" placeholder="Ej: Flyback, T-Con, Fuente conmutada..." required>
                    </div>
                    <button type="submit" class="btn-primary">🔍 Buscar Información</button>
                </form>
            </section>

            <!-- Resultados -->
            <section class="card" id="resultSection" style="display: none;">
                <h2>📚 Explicación</h2>
                
                <button id="btnVoice" class="btn-voice-simple" title="Escuchar explicación">
                    <span class="voice-icon-simple">🔊</span>
                    <span id="voiceLabel">Escuchar explicación</span>
                </button>
                
                <div class="result-content" id="resultContent"></div>
                <button id="newSearchBtn" class="btn-secondary">🔄 Nueva Búsqueda</button>
            </section>
        </main>

        <footer class="app-footer">
            <p>Tutor Virtual v1.0 — Powered by Groq AI</p>
        </footer>
    </div>

    <!-- ========================================== -->
    <!-- VARIABLES GLOBALES PARA JAVASCRIPT -->
    <!-- ========================================== -->
    <script>
        // Pasamos los datos seguros de PHP a JavaScript
        const currentUser = '<?= $username ?>';
        const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;
        
        console.log("Sesión activa: " + currentUser + " (Admin: " + isAdmin + ")");
    </script>

    <!-- Tu script original (Cargado UNA sola vez al final) -->
    <script src="app.js"></script>
</body>
</html>