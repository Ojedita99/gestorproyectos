<?php
session_start();

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header('Location: index.php');
    exit;
}

// Incluir PHPMailer
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';
require __DIR__ . '/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener los datos del formulario
    $nombre_proyecto = $_POST['nombre_proyecto'];
    $usuario_id = $_POST['usuario_id']; // ID del usuario asignado
    $tareas_array = $_POST['tareas']; // Tareas como array
    $fechas_tareas_array = $_POST['fechas_tareas']; // Fechas límites como array
    $fecha_limite_proyecto = $_POST['fecha_limite']; // Fecha límite del proyecto

    // Leer el archivo JSON de proyectos
    $proyectos_file = file_get_contents('json/proyectos.json');
    $proyectos = json_decode($proyectos_file, true);

    // Generar un nuevo ID único para el proyecto
    $nuevo_id = 1;
    if (!empty($proyectos)) {
        // Obtener el último ID y sumar 1
        $ultimo_proyecto = end($proyectos);
        $nuevo_id = $ultimo_proyecto['id'] + 1;
    }

    // Crear el nuevo proyecto con tareas y estado por defecto
    $nuevo_proyecto = [
        'id' => $nuevo_id,
        'nombre' => $nombre_proyecto,
        'usuario_id' => $usuario_id, // Asignar usuario
        'fecha_limite' => $fecha_limite_proyecto, // Agregar fecha límite del proyecto
        'estado' => 'En Proceso', // Estado por defecto del proyecto
        'tareas' => []
    ];

    // Agregar las tareas al proyecto
    foreach ($tareas_array as $index => $descripcion_tarea) {
        $nuevo_proyecto['tareas'][] = [
            'id' => $index + 1, // Generar un ID para la tarea
            'descripcion' => trim($descripcion_tarea), // Limpiar espacios
            'fecha_limite' => trim($fechas_tareas_array[$index]), // Agregar la fecha límite de la tarea
            'finalizada' => false // Inicialmente, todas las tareas están sin completar
        ];
    }

    // Agregar el nuevo proyecto al array de proyectos
    $proyectos[] = $nuevo_proyecto;

    // Guardar los proyectos actualizados en el archivo JSON
    file_put_contents('json/proyectos.json', json_encode($proyectos, JSON_PRETTY_PRINT));

    // Leer el archivo JSON de usuarios para obtener el correo del usuario asignado
    $usuarios_file = file_get_contents('json/usuarios.json');
    $usuarios = json_decode($usuarios_file, true);
    $usuario_email = '';

    // Buscar el email del usuario por su ID
    foreach ($usuarios as $usuario) {
        if ($usuario['id'] == $usuario_id) {
            $usuario_email = $usuario['correo']; // Asegúrate de que esta clave existe en el JSON
            break;
        }
    }

    // Enviar notificación al usuario asignado
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = 'ojedita99s@gmail.com'; // Tu dirección de correo
        $mail->Password = 'Futbol99!'; // Tu contraseña de correo
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Destinatarios
        $mail->setFrom('troelosxddd@gmail.com', 'Marko');
        $mail->addAddress($usuario_email); // Agregar el correo del usuario asignado

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = 'Nuevo Proyecto Asignado: ' . $nombre_proyecto;
        $mail->Body = 'Se te ha asignado un nuevo proyecto: <b>' . $nombre_proyecto . '</b>. La fecha límite es ' . $fecha_limite_proyecto . '.';

        // Enviar el correo
        $mail->send();
        echo 'El correo ha sido enviado';
    } catch (Exception $e) {
        echo "El correo no se pudo enviar. Error: {$mail->ErrorInfo}";
    }

    // Redirigir al dashboard del administrador
    header('Location: dashboard_admin.php');
    exit;
}

// Leer el archivo JSON de usuarios para asignar proyectos
$usuarios_file = file_get_contents('json/usuarios.json');
$usuarios = json_decode($usuarios_file, true);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Proyecto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        // Función para agregar una nueva tarea a la lista
        function agregarTarea() {
            var tareaInput = document.getElementById('tarea');
            var fechaInput = document.getElementById('fecha_tarea');
            var listaTareas = document.getElementById('listaTareas');

            if (tareaInput.value.trim() !== '' && fechaInput.value.trim() !== '') {
                var nuevaTarea = document.createElement('li');
                nuevaTarea.className = 'list-group-item';
                nuevaTarea.textContent = tareaInput.value.trim() + " - Fecha límite: " + fechaInput.value;

                // Crear inputs ocultos para agregar la tarea al formulario
                var inputOculto = document.createElement('input');
                inputOculto.type = 'hidden';
                inputOculto.name = 'tareas[]';
                inputOculto.value = tareaInput.value.trim();

                var inputFechaOculto = document.createElement('input');
                inputFechaOculto.type = 'hidden';
                inputFechaOculto.name = 'fechas_tareas[]';
                inputFechaOculto.value = fechaInput.value.trim();

                nuevaTarea.appendChild(inputOculto);
                nuevaTarea.appendChild(inputFechaOculto);
                listaTareas.appendChild(nuevaTarea);

                // Limpiar los campos de entrada de tareas
                tareaInput.value = '';
                fechaInput.value = '';
            } else {
                alert('Por favor, completa tanto la tarea como la fecha límite.');
            }
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <h2>Crear Nuevo Proyecto</h2>
        <form method="POST" action="">
            <div class="mb-3">
                <label for="nombre_proyecto" class="form-label">Nombre del Proyecto</label>
                <input type="text" class="form-control" id="nombre_proyecto" name="nombre_proyecto" required>
            </div>
            <div class="mb-3">
                <label for="usuario_id" class="form-label">ID del Usuario Asignado</label>
                <select class="form-control" id="usuario_id" name="usuario_id" required>
                    <option value="" disabled selected>Seleccione un usuario</option>
                    <?php foreach ($usuarios as $usuario): ?>
                        <?php if ($usuario['rol'] === 'usuario'): // Solo mostrar usuarios normales ?>
                            <option value="<?= htmlspecialchars($usuario['id']) ?>">
                                <?= htmlspecialchars($usuario['usuario']) ?> (ID: <?= htmlspecialchars($usuario['id']) ?>)
                            </option>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Sección de fecha límite del proyecto -->
            <div class="mb-3">
                <label for="fecha_limite" class="form-label">Fecha Límite del Proyecto</label>
                <input type="date" class="form-control" id="fecha_limite" name="fecha_limite" required>
            </div>

            <!-- Sección de agregar tareas -->
            <div class="mb-3">
                <label for="tarea" class="form-label">Agregar Tarea</label>
                <input type="text" class="form-control" id="tarea" placeholder="Escribe una tarea y pulsa 'Agregar'">
                <label for="fecha_tarea" class="form-label mt-2">Fecha Límite de la Tarea</label>
                <input type="date" class="form-control" id="fecha_tarea" placeholder="Selecciona la fecha límite">
                <button type="button" class="btn btn-primary mt-2" onclick="agregarTarea()">Agregar Tarea</button>
            </div>

            <ul class="list-group mb-3" id="listaTareas">
                <!-- Aquí se agregan las tareas dinámicamente -->
            </ul>

            <button type="submit" class="btn btn-success">Crear Proyecto</button>
        </form>
    </div>
</body>
</html>
