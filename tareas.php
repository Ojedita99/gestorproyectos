<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    header('Location: index.php');
    exit;
}

// Verificar si se enviaron los datos de la tarea
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proyecto_id']) && isset($_POST['tarea_id'])) {
    $proyecto_id = $_POST['proyecto_id'];
    $tarea_id = $_POST['tarea_id'];

    // Leer los proyectos desde el archivo JSON
    $proyectos_file = file_get_contents('json/proyectos.json');
    $proyectos = json_decode($proyectos_file, true); // Asegúrate de que sea un array asociativo

    // Buscar el proyecto y la tarea específica
    foreach ($proyectos as &$proyecto) {
        if ($proyecto['id'] == $proyecto_id) {
            foreach ($proyecto['tareas'] as &$tarea) {
                if ($tarea['id'] == $tarea_id) {
                    // Marcar la tarea como finalizada
                    $tarea['finalizada'] = true;
                    break;
                }
            }
            break;
        }
    }

    // Guardar los proyectos actualizados en el archivo JSON
    file_put_contents('json/proyectos.json', json_encode($proyectos, JSON_PRETTY_PRINT));

    // Redirigir de vuelta al dashboard
    header('Location: dashboard_usuario.php');
    exit;
}
?>
