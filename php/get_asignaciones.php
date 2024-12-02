<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("
        SELECT 
            ca.id, 
            m.nombre AS materia_nombre, 
            u.nombre || ' ' || u.apellido_paterno AS docente_nombre, 
            ca.periodo_academico, 
            ca.paralelo, 
            ca.dia_semana, 
            ca.hora_inicio, 
            ca.hora_fin, 
            ca.aula
        FROM curso_asignaciones ca
        JOIN materias m ON ca.materia_id = m.id
        JOIN usuarios u ON ca.docente_id = u.id
    ");
    $asignaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($asignaciones);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error retrieving course assignments: ' . $e->getMessage()]);
}
?>