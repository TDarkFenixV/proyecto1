<?php
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Assignment ID is required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("DELETE FROM curso_asignaciones WHERE id = :id");
    $result = $stmt->execute(['id' => $_GET['id']]);
    
    if ($result) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete assignment']);
    }
} catch(PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>