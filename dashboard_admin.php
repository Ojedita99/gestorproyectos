<?php
session_start();

// Verificar si el usuario está logueado y si es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Leer los proyectos desde el archivo JSON
$proyectos_file = file_get_contents('json/proyectos.json');
$proyectos = json_decode($proyectos_file, true); // Asegúrate de que sea un array asociativo

// Manejo de acciones (eliminar, asignar, agregar tareas)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'];
    $proyecto_id = $_POST['proyecto_id'];

    // Leer el archivo de proyectos
    $proyectos = json_decode(file_get_contents('json/proyectos.json'), true);

    if ($accion === 'eliminar') {
        // Eliminar el proyecto
        $proyectos = array_filter($proyectos, function($proyecto) use ($proyecto_id) {
            return $proyecto['id'] != $proyecto_id;
        });
        file_put_contents('json/proyectos.json', json_encode(array_values($proyectos), JSON_PRETTY_PRINT));
    } elseif ($accion === 'asignar') {
        // Asignar proyecto a un usuario
        $usuario_id = $_POST['usuario_id'];
        foreach ($proyectos as &$proyecto) {
            if ($proyecto['id'] == $proyecto_id) {
                $proyecto['usuario_id'] = $usuario_id;
                break;
            }
        }
        file_put_contents('json/proyectos.json', json_encode($proyectos, JSON_PRETTY_PRINT));
    } elseif ($accion === 'agregar_tarea') {
        // Agregar tarea al proyecto
        $tarea = $_POST['tarea'];
        foreach ($proyectos as &$proyecto) {
            if ($proyecto['id'] == $proyecto_id) {
                $proyecto['tareas'][] = [
                    'id' => count($proyecto['tareas']) + 1,
                    'descripcion' => $tarea,
                    'finalizada' => false
                ];
                break;
            }
        }
        file_put_contents('json/proyectos.json', json_encode($proyectos, JSON_PRETTY_PRINT));
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/admin_style.css"> <!-- Incluye tu archivo de estilos -->
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Dashboard Administrador</h2>
        <h3>Proyectos</h3>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (is_array($proyectos) && !empty($proyectos)): ?>
                    <?php foreach ($proyectos as $proyecto): ?>
                        <tr>
                            <td><?= htmlspecialchars($proyecto['id']) ?></td>
                            <td><?= htmlspecialchars($proyecto['nombre']) ?></td>
                            <td><?= htmlspecialchars(ucfirst($proyecto['estado'])) ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="accion" value="eliminar">
                                    <input type="hidden" name="proyecto_id" value="<?= htmlspecialchars($proyecto['id']) ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que quieres eliminar este proyecto?');">Eliminar</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="accion" value="asignar">
                                    <input type="hidden" name="proyecto_id" value="<?= htmlspecialchars($proyecto['id']) ?>">
                                    <input type="text" name="usuario_id" placeholder="ID Usuario" required class="form-control form-control-sm d-inline" style="width: 120px;">
                                    <button type="submit" class="btn btn-secondary btn-sm">Asignar</button>
                                </form>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="accion" value="agregar_tarea">
                                    <input type="hidden" name="proyecto_id" value="<?= htmlspecialchars($proyecto['id']) ?>">
                                    <input type="text" name="tarea" placeholder="Nueva Tarea" required class="form-control form-control-sm d-inline" style="width: 150px;">
                                    <button type="submit" class="btn btn-success btn-sm">Agregar Tarea</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="text-center">No hay proyectos disponibles.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
        </div>

        <div class="text-center">
            <a href="crear_proyecto.php" class="btn btn-primary">Crear Nuevo Proyecto</a>
        </div>
    </div>
    
    <footer class="text-center mt-4">
        <p>&copy; <?= date('Y') ?> Tu Plataforma de Gestión</p>
    </footer>
</body>
</html>
