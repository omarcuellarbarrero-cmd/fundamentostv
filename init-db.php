<?php
// Script de inicialización de base de datos
$dbPath = __DIR__ . '/data/users.db';

// Crear directorio si no existe
if (!file_exists(__DIR__ . '/data')) {
    mkdir(__DIR__ . '/data', 0777, true);
}

try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Crear tabla de usuarios
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT UNIQUE NOT NULL,
        password_hash TEXT NOT NULL,
        is_admin INTEGER DEFAULT 0,
        activo INTEGER DEFAULT 1,
        fecha_creacion DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Crear usuario admin por defecto (admin/admin2026)
    $adminPassword = password_hash('admin2026', PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (username, password_hash, is_admin, activo) 
                          VALUES ('admin', ?, 1, 1)");
    $stmt->execute([$adminPassword]);
    
    echo "✅ Base de datos creada exitosamente. Usuario admin creado.";
    
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>