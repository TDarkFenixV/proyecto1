<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Teacher ID is required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT email, telefono, nombre, apellido_paterno FROM usuarios WHERE id = :id AND tipo_usuario = 'teacher' AND activo = true");
    $stmt->execute(['id' => $_GET['id']]);
    $docente = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$docente) {
        http_response_code(404);
        echo json_encode(['error' => 'Teacher not found']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode($docente);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error retrieving teacher details: ' . $e->getMessage()]);
}
?>