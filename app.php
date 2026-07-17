<?php
session_start();

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
    
    <!-- Estilos adicionales para la sesión -->
    <style>
        .user-info {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
            z-index: 1000;
        }
        .user-name {
            color: white;
            font-weight: 600;
        }
        .btn-logout {
            background: #e74c3c;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s;
        }
        .btn-logout:hover {
            background: #c0392b;
        }
    </style>
</head>
<body>
    <!-- Información del usuario -->
    <div class="user-info">
        <span class="user-name">👋 Hola, <?= $username ?></span>
        <a href="logout.php" class="btn-logout">🚪 Cerrar Sesión</a>
    </div>

    <!-- ========================================== -->
    <!-- AQUÍ PEGA EL CONTENIDO DE app.html -->
    <!-- ========================================== -->
    
    <!-- Copia TODO lo que estaba entre <body> y </body> en app.html -->
    <!-- y pégalo aquí, EXCEPTO cualquier botón de logout que ya tengas -->
    
   <div class="container">
        <header class="app-header">
            <div class="logo-container">
                <img src="Logo-nuevo-metodo-oc.webp" alt="Logo" class="logo-main">
            </div>
            <h1>🔧 Fundamentación Técnica</h1>
            <p class="subtitle">Asistente Técnico Virtual</p>
            <button id="logoutBtn" class="btn-logout">🚪 Salir</button>
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
        <footer class="app-footer">
            <p>Asistente Técnico v1.0 — Powered by Groq AI</p>
        </footer>

    <script src="app.js"></script>

    <!-- ========================================== -->
    <!-- FIN DEL CONTENIDO DE app.html -->
    <!-- ========================================== -->

    <!-- Tus scripts originales -->
    <script src="app.js"></script>
    
    <!-- Script para actualizar el nombre de usuario si es necesario -->
    <script>
        // Si tu app.js necesita el nombre del usuario, puedes pasarlo así:
        const currentUser = '<?= $username ?>';
        const isAdmin = <?= $is_admin ? 'true' : 'false' ?>;
        
        // Si necesitas hacer algo especial para admins:
        if (isAdmin) {
            console.log('Usuario administrador detectado');
            // Puedes agregar botones adicionales aquí si es necesario
        }
    </script>
</body>
</html>
