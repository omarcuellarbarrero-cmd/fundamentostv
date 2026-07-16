<?php
session_start();
$error = '';

if (isset($_SESSION['user_id']) && !empty($_SESSION['is_admin'])) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtolower(trim($_POST['username']));
    $password = $_POST['password'];
    
    $dbPath = __DIR__ . '/data/users.db';
    try {
        $db = new PDO('sqlite:' . $dbPath);
        $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND activo = 1 AND is_admin = 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = true;
            header('Location: admin.php');
            exit;
        } else {
            $error = 'Credenciales de administrador incorrectas.';
        }
    } catch (PDOException $e) {
        $error = 'Error de conexión.';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Fundamentos TV</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 40px;
            max-width: 450px;
            width: 100%;
            text-align: center;
        }
        .logo { max-width: 180px; height: auto; margin-bottom: 15px; }
        h1 { color: #2c3e50; font-size: 28px; margin-bottom: 10px; }
        .subtitle { color: #666; font-size: 14px; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; text-align: left; }
        label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 15px;
            background: #f8f9fa;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            outline: none;
            border-color: #2c3e50;
            background: white;
            box-shadow: 0 0 0 3px rgba(44, 62, 80, 0.1);
        }
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
        }
        .error {
            background: #fee;
            color: #c33;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            color: #999;
            font-size: 12px;
        }
        .user-link {
            margin-top: 15px;
            font-size: 12px;
        }
        .user-link a {
            color: #2c3e50;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <img src="Logo-nuevo-metodo-oc.webp" alt="Fundamentos TV" class="logo" onerror="this.style.display='none'">
        <h1>Panel de Administración</h1>
        <p class="subtitle">Gestión de Usuarios y Contenido</p>
        
        <?php if ($error): ?>
            <div class="error">❌ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label>👤 Usuario Admin:</label>
                <input type="text" name="username" placeholder="Escribe tu usuario" required autocomplete="off">
            </div>
            
            <div class="form-group">
                <label>🔒 Contraseña:</label>
                <input type="password" name="password" placeholder="Escribe tu contraseña" required>
            </div>
            
            <button type="submit" class="btn-login">Ingresar como Admin</button>
        </form>
        
        <div class="user-link">
            ¿Eres estudiante? <a href="index.php">Accede aquí</a>
        </div>
        
        <div class="footer">
            Fundamentos TV - Panel Admin © 2026
        </div>
    </div>
</body>
</html>