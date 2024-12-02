<?php
// Configuración del servidor
require_once 'config.php';
session_start();

// Función para enviar respuestas JSON
function sendJsonResponse($success, $data = null, $error = null) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'cursos' => $data,
        'error' => $error,
    ]);
    exit;
}

// Validar sesión del usuario
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'teacher') {
    sendJsonResponse(false, null, 'No autorizado');
}

$docente_id = $_SESSION['user_id'];

try {
    // Obtener conexión a la base de datos
    $conn = getDBConnection();

    // Consulta para obtener cursos del docente
    $query = "
        SELECT 
            ca.id AS asignacion_id,
            m.codigo AS codigo_materia,
            m.nombre AS nombre_materia,
            ca.paralelo,
            ca.periodo_academico,
            CASE ca.dia_semana 
                WHEN 1 THEN 'Lunes'
                WHEN 2 THEN 'Martes'
                WHEN 3 THEN 'Miércoles'
                WHEN 4 THEN 'Jueves'
                WHEN 5 THEN 'Viernes'
                WHEN 6 THEN 'Sábado'
                WHEN 7 THEN 'Domingo'
            END AS dia_semana,
            ca.hora_inicio,
            ca.hora_fin,
            ca.aula
        FROM curso_asignaciones ca
        JOIN materias m ON ca.materia_id = m.id
        WHERE ca.docente_id = :docente_id
        ORDER BY ca.periodo_academico, m.nombre";

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':docente_id', $docente_id, PDO::PARAM_INT);
    $stmt->execute();

    $cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Respuesta exitosa con datos
    sendJsonResponse(true, $cursos);

} catch (Exception $e) {
    // Manejo de errores
    sendJsonResponse(false, null, 'Error al obtener los cursos: ' . $e->getMessage());
}
?>
