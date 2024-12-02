<?php
require_once 'config.php';

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->query("SELECT id, codigo, nombre, descripcion, creditos FROM materias WHERE activo = true");
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($materias);
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error retrieving courses: ' . $e->getMessage()]);
}
?>