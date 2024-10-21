<?php
session_start();

// Verificar si el usuario está logueado y si es un usuario normal
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'usuario') {
    header('Location: index.php');
    exit;
}

// Obtener el ID del usuario logueado
$usuario_id = $_SESSION['usuario']['id'];

// Leer los proyectos desde el archivo JSON
$proyectos_file = file_get_contents('json/proyectos.json');
$proyectos = json_decode($proyectos_file, true); // Asegúrate de que sea un array asociativo

// Filtrar proyectos asignados al usuario
$proyectos_asignados = array_filter($proyectos, function($proyecto) use ($usuario_id) {
    return isset($proyecto['usuario_id']) && $proyecto['usuario_id'] == $usuario_id;
});

// Manejar la finalización de una tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tarea_id']) && isset($_POST['proyecto_id'])) {
    $tarea_id = $_POST['tarea_id']; // ID de la tarea a completar
    $proyecto_id = $_POST['proyecto_id']; // ID del proyecto

    // Marcar la tarea como completada
    foreach ($proyectos as &$proyecto) {
        if ($proyecto['id'] == $proyecto_id) {
            foreach ($proyecto['tareas'] as &$tarea) {
                if ($tarea['id'] == $tarea_id) {
                    $tarea['finalizada'] = true; // Marcar como completada
                }
            }
            // Verificar si todas las tareas están completas
            $todas_completadas = true;
            foreach ($proyecto['tareas'] as $tarea) {
                if (!$tarea['finalizada']) {
                    $todas_completadas = false;
                    break;
                }
            }
            // Actualizar el estado del proyecto si todas las tareas están completas
            if ($todas_completadas) {
                $proyecto['estado'] = 'finalizado'; // Cambiar el estado a finalizado
            }
        }
    }

    // Guardar los cambios en el archivo JSON
    file_put_contents('json/proyectos.json', json_encode($proyectos, JSON_PRETTY_PRINT));
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Usuario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css"> <!-- Asegúrate de tener el archivo style.css -->
    
    <script>
        // Función para mostrar/ocultar tareas de un proyecto
        function toggleTareas(proyectoId) {
            var tareasDiv = document.getElementById('tareas-' + proyectoId);
            tareasDiv.style.display = tareasDiv.style.display === 'none' ? 'block' : 'none';
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Bienvenido, <?= htmlspecialchars($_SESSION['usuario']['usuario']) ?></h2>
        <h3 class="mt-4">Tus Proyectos Asignados</h3>
        
        <?php if (is_array($proyectos_asignados) && !empty($proyectos_asignados)): ?>
            <ul class="list-group mt-3">
                <?php foreach ($proyectos_asignados as $proyecto): ?>
                    <li class="list-group-item">
                        <strong><?= htmlspecialchars($proyecto['nombre']) ?></strong>
                        <button class="btn btn-link" onclick="toggleTareas(<?= htmlspecialchars($proyecto['id']) ?>)">Ver Tareas</button>
                        
                        <div id="tareas-<?= htmlspecialchars($proyecto['id']) ?>" style="display: none;" class="mt-3">
                            <ul class="list-group">
                                <?php foreach ($proyecto['tareas'] as $tarea): ?>
                                    <li class="list-group-item">
                                        <?= htmlspecialchars($tarea['descripcion']) ?>
                                        <?php if ($tarea['finalizada']): ?>
                                            <span class="badge bg-success">Completada</span>
                                        <?php else: ?>
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="proyecto_id" value="<?= htmlspecialchars($proyecto['id']) ?>">
                                                <input type="hidden" name="tarea_id" value="<?= htmlspecialchars($tarea['id']) ?>">
                                                <button type="submit" class="btn btn-sm btn-success">Marcar como Completada</button>
                                            </form>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="mt-4 text-center">No tienes proyectos asignados.</p>
        <?php endif; ?>
        
        <div class="text-center mt-5">
            <a href="index.php" class="btn btn-secondary">Volver al Inicio</a>
        </div>
    </div>

    <footer class="text-center mt-5">
        <p>&copy; <?= date('Y') ?> Tu Plataforma de Gestión</p>
    </footer>
</body>
</html>
