<?php
session_start();
$error = '';

// Manejo de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarios_file = file_get_contents('json/usuarios.json');
    $usuarios = json_decode($usuarios_file, true); // Asegúrate de que sea un array asociativo

    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    // Verificar si las credenciales son correctas
    foreach ($usuarios as $usuario_data) {
        if ($usuario_data['usuario'] === $usuario && $usuario_data['contraseña'] === $contraseña) {
            $_SESSION['usuario'] = $usuario_data; // Guardar datos de usuario en la sesión
            header('Location: dashboard_' . $usuario_data['rol'] . '.php');
            exit;
        }
    }

    $error = 'Usuario o contraseña incorrectos.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inicio de Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/login_style.css"> <!-- Incluye tu archivo de estilos -->
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <h2 class="text-center">Iniciar Sesión</h2>

                <?php if ($error): ?>
                    <div class="alert alert-danger" role="alert">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="mb-3">
                        <label for="usuario" class="form-label">Usuario</label>
                        <input type="text" class="form-control" id="usuario" name="usuario" required>
                    </div>
                    <div class="mb-3">
                        <label for="contraseña" class="form-label">Contraseña</label>
                        <input type="password" class="form-control" id="contraseña" name="contraseña" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
                    <a href="registro.php" class="btn btn-link">Registrarse</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
