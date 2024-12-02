<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT id, nombre, apellido_paterno, email, telefono FROM usuarios WHERE tipo_usuario = 'teacher' AND activo = true");
    $docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($docentes);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error retrieving teachers: ' . $e->getMessage()]);
}
?>