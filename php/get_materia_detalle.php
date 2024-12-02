<?php
require_once 'config.php';

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Course ID is required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT codigo, nombre, descripcion, creditos FROM materias WHERE id = :id AND activo = true");
    $stmt->execute(['id' => $_GET['id']]);
    $materia = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$materia) {
        http_response_code(404);
        echo json_encode(['error' => 'Course not found']);
        exit;
    }
    
    header('Content-Type: application/json');
    echo json_encode($materia);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error retrieving course details: ' . $e->getMessage()]);
}
?>