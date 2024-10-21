<?php
session_start();
$error = '';

// Manejo de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarios_file = 'json/usuarios.json';
    $usuarios = json_decode(file_get_contents($usuarios_file), true); // Asegúrate de que sea un array asociativo

    $nuevo_usuario = [
        'usuario' => $_POST['usuario'],
        'contraseña' => $_POST['contraseña'],
        'rol' => 'usuario', // Asignar rol por defecto
    ];

    // Comprobar si el usuario desea registrarse como admin
    if (isset($_POST['admin']) && $_POST['admin'] === 'on') {
        // Contraseña para permitir registro como admin
        $admin_password = '1234'; // Cambia esto a la contraseña deseada

        // Verificar la contraseña de admin
        if ($_POST['admin_password'] === $admin_password) {
            $nuevo_usuario['rol'] = 'admin'; // Cambiar rol a admin
        } else {
            $error = 'Lo siento, no puedes ser admin. Contraseña incorrecta.';
        }
    }

    // Agregar nuevo usuario al array si no hay errores
    if (empty($error)) {
        $usuarios[] = $nuevo_usuario;

        // Guardar de nuevo el archivo JSON
        file_put_contents($usuarios_file, json_encode($usuarios, JSON_PRETTY_PRINT));

        // Redirigir al inicio de sesión después del registro
        header('Location: index.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/registro.css"> <!-- Enlace al archivo CSS -->
</head>
<body>
    <div class="container mt-5">
        <h2>Registrarse</h2>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST">
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" name="usuario" id="usuario" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="password" name="contraseña" id="contraseña" class="form-control" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="admin" name="admin">
                <label class="form-check-label" for="admin">Registrarse como Admin</label>
            </div>
            <div class="mb-3" id="admin-password-field" style="display: none;">
                <label for="admin_password" class="form-label">Contraseña Admin</label>
                <input type="password" name="admin_password" id="admin_password" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary btn-block">Registrar</button>
        </form>
        <a href="index.php" class="btn btn-link mt-3">Iniciar Sesión</a>
    </div>

    <script>
        // Mostrar/ocultar el campo de contraseña admin
        const adminCheckbox = document.getElementById('admin');
        const adminPasswordField = document.getElementById('admin-password-field');

        adminCheckbox.addEventListener('change', function() {
            adminPasswordField.style.display = adminCheckbox.checked ? 'block' : 'none';
        });
    </script>
</body>
</html>
