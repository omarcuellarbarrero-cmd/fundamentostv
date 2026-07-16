<?php
session_start();

// 🔒 SEGURIDAD: Si no hay sesión o NO es admin, fuera de aquí.
if (!isset($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
    header('Location: admin-login.php');
    exit;
}

$dbPath = __DIR__ . '/data/users.db';
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de BD: " . $e->getMessage());
}

$message = '';
$edit_user = null;

// ==========================================
// VERIFICAR SI ESTAMOS EDITANDO (GET)
// ==========================================
if (isset($_GET['edit_user'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit_user']]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ==========================================
// PROCESAR ACCIONES (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // --- AGREGAR USUARIO ---
    if ($action === 'add_user') {
        $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        try {
            $stmt = $db->prepare("INSERT INTO users (username, password_hash, is_admin, activo) VALUES (?, ?, ?, 1)");
            $stmt->execute([
                strtolower(trim($_POST['username'])), 
                $hash, 
                isset($_POST['is_admin']) ? 1 : 0
            ]);
            $message = "✅ Usuario creado correctamente.";
        } catch (PDOException $e) {
            $message = "❌ Error: El nombre de usuario ya existe.";
        }
    }
    // --- ACTUALIZAR USUARIO ---
    elseif ($action === 'update_user') {
        if (empty($_POST['password'])) {
            // Sin cambio de contraseña
            $stmt = $db->prepare("UPDATE users SET username = ?, is_admin = ? WHERE id = ?");
            $stmt->execute([
                strtolower(trim($_POST['username'])), 
                isset($_POST['is_admin']) ? 1 : 0, 
                $_POST['id']
            ]);
        } else {
            // Con cambio de contraseña
            $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $stmt = $db->prepare("UPDATE users SET username = ?, password_hash = ?, is_admin = ? WHERE id = ?");
            $stmt->execute([
                strtolower(trim($_POST['username'])), 
                $hash, 
                isset($_POST['is_admin']) ? 1 : 0, 
                $_POST['id']
            ]);
        }
        $message = "✅ Usuario actualizado correctamente.";
    }
    // --- ELIMINAR USUARIO ---
    elseif ($action === 'delete_user') {
        // Protección: No permitir borrar administradores desde este formulario simple
        $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND is_admin = 0");
        $stmt->execute([$_POST['id']]);
        $message = "🗑️ Usuario eliminado.";
    }

    // Redirigir para evitar reenvío de formulario
    header('Location: admin.php?msg=' . urlencode($message));
    exit;
}

if (isset($_GET['msg'])) {
    $message = $_GET['msg'];
}

// ==========================================
// OBTENER LISTA DE USUARIOS
// ==========================================
$users = $db->query("SELECT * FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Fundamentos TV</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f4f4f9;
            padding: 20px;
        }
        .container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #eee;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }
        .header h2 { color: #2c3e50; }
        .btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            color: white;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            display: inline-block;
            transition: opacity 0.2s;
        }
        .btn:hover { opacity: 0.9; }
        .btn-primary { background: #3498db; }
        .btn-warning { background: #f39c12; }
        .btn-danger { background: #e74c3c; }
        .btn-success { background: #27ae60; }
        
        .message {
            padding: 12px;
            background: #d4edda;
            color: #155724;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        th {
            background: #f8f9fa;
            color: #2c3e50;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 13px;
        }
        tr:hover { background: #f8f9fa; }
        
        .form-section {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid #e0e0e0;
        }
        .form-section h3 { margin-bottom: 15px; color: #2c3e50; }
        .form-group { margin-bottom: 15px; }
        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-weight: 600;
            color: #555;
            font-size: 14px;
        }
        .form-group input[type="text"],
        .form-group input[type="password"] {
            width: 100%;
            max-width: 400px;
            padding: 10px 12px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 14px;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
        }
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }
        .edit-mode {
            background: #fff3cd;
            padding: 15px;
            border: 1px solid #ffeeba;
            border-radius: 8px;
            margin-bottom: 15px;
            color: #856404;
        }
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-admin { background: #e74c3c; color: white; }
        .badge-user { background: #3498db; color: white; }
        
        @media (max-width: 768px) {
            .container { padding: 15px; }
            table { font-size: 13px; }
            th, td { padding: 8px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>🛠️ Panel de Administración</h2>
            <div>
                <span style="margin-right: 15px; color: #555;">Hola, <?= htmlspecialchars($_SESSION['username']) ?></span>
                <a href="logout.php" class="btn btn-danger">🚪 Cerrar Sesión</a>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="message"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <!-- FORMULARIO DE USUARIO -->
        <div class="form-section">
            <h3><?= $edit_user ? '✏️ Editando Usuario: ' . htmlspecialchars($edit_user['username']) : '➕ Agregar Nuevo Usuario' ?></h3>
            
            <?php if ($edit_user): ?>
                <div class="edit-mode">
                    Modo edición. <a href="admin.php" style="color: #856404; font-weight: bold;">Cancelar edición</a>
                </div>
            <?php endif; ?>

            <form method="POST">
                <input type="hidden" name="action" value="<?= $edit_user ? 'update_user' : 'add_user' ?>">
                <?php if ($edit_user): ?>
                    <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label>Nombre de Usuario:</label>
                    <input type="text" name="username" value="<?= $edit_user ? htmlspecialchars($edit_user['username']) : '' ?>" required autocomplete="off">
                </div>
                
                <div class="form-group">
                    <label>Contraseña <?= $edit_user ? '(Dejar vacío para mantener la actual)' : '' ?>:</label>
                    <input type="password" name="password" <?= $edit_user ? '' : 'required' ?>>
                </div>
                
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="is_admin" id="is_admin" value="1" <?= ($edit_user && $edit_user['is_admin']) ? 'checked' : '' ?>>
                    <label for="is_admin" style="margin: 0; cursor: pointer;">Es Administrador</label>
                </div>
                
                <div style="margin-top: 20px;">
                    <button type="submit" class="btn <?= $edit_user ? 'btn-warning' : 'btn-success' ?>">
                        <?= $edit_user ? '💾 Actualizar Usuario' : '💾 Crear Usuario' ?>
                    </button>
                    <?php if ($edit_user): ?>
                        <a href="admin.php" class="btn btn-primary">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- TABLA DE USUARIOS -->
        <h3 style="color: #2c3e50; margin-bottom: 10px;">👥 Lista de Usuarios Registrados</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Usuario</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['id'] ?></td>
                    <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                    <td>
                        <span class="badge <?= $user['is_admin'] ? 'badge-admin' : 'badge-user' ?>">
                            <?= $user['is_admin'] ? '👑 Admin' : '🔧 Estudiante' ?>
                        </span>
                    </td>
                    <td><?= $user['activo'] ? '✅ Activo' : '❌ Inactivo' ?></td>
                    <td>
                        <a href="?edit_user=<?= $user['id'] ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">✏️ Editar</a>
                        
                        <?php if (!$user['is_admin']): ?>
                        <form method="POST" style="display:inline;" onsubmit="return confirm('¿Estás seguro de eliminar a este usuario?');">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="id" value="<?= $user['id'] ?>">
                            <button type="submit" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;">🗑️ Eliminar</button>
                        </form>
                        <?php else: ?>
                            <span style="color: #999; font-size: 12px; margin-left: 5px;">(Protegido)</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>