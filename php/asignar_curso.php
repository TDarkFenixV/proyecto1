<?php
require_once 'config.php';

header('Content-Type: application/json');

// Get JSON input
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Validate required fields
$requiredFields = ['materia_id', 'docente_id', 'periodo_academico', 'paralelo', 'dia_semana', 'hora_inicio', 'hora_fin', 'aula'];
foreach ($requiredFields as $field) {
    if (!isset($data[$field]) || empty($data[$field])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Missing or empty field: $field"]);
        exit;
    }
}

try {
    $pdo = getDBConnection();
    
    // Prepare SQL statement
    $stmt = $pdo->prepare("
        INSERT INTO curso_asignaciones 
        (materia_id, docente_id, periodo_academico, paralelo, dia_semana, hora_inicio, hora_fin, aula) 
        VALUES 
        (:materia_id, :docente_id, :periodo_academico, :paralelo, :dia_semana, :hora_inicio, :hora_fin, :aula)
    ");
    
    // Execute the statement
    $result = $stmt->execute([
        ':materia_id' => $data['materia_id'],
        ':docente_id' => $data['docente_id'],
        ':periodo_academico' => $data['periodo_academico'],
        ':paralelo' => $data['paralelo'],
        ':dia_semana' => $data['dia_semana'],
        ':hora_inicio' => $data['hora_inicio'],
        ':hora_fin' => $data['hora_fin'],
        ':aula' => $data['aula']
    ]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to assign course']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>