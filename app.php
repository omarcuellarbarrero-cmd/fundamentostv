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
    <style>
        body { font-family: 'Segoe UI', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .container { background: white; max-width: 600px; width: 100%; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); text-align: center; }
        h1 { color: #2c3e50; margin-bottom: 10px; }
        .btn { display: inline-block; padding: 12px 25px; background: #e74c3c; color: white; text-decoration: none; border-radius: 8px; margin-top: 20px; font-weight: 600; }
        .btn:hover { background: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>¡Bienvenido, <?= $username ?>!</h1>
        <p>Has iniciado sesión correctamente en Fundamentos TV.</p>
        <p>El archivo app.php ahora existe y el error 404 ha desaparecido.</p>
        
        <?php if ($is_admin): ?>
            <br><a href="admin.php" style="color: #3498db; font-weight: bold;">Ir al Panel de Administración</a>
        <?php endif; ?>
        
        <br>
        <a href="logout.php" class="btn">🚪 Cerrar Sesión</a>
    </div>
</body>
</html>