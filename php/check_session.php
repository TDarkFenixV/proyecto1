<?php
// check_session.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

// Verificar si existe una sesión activa
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    echo json_encode([
        'success' => false,
        'message' => 'No session found'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, tipo_usuario, nombre, apellido_paterno, apellido_materno, 
                                 ic, email, sexo, telefono, fecha_registro, fecha_nacimiento
                          FROM usuarios 
                          WHERE id = :id AND activo = true");
    $stmt->execute(['id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Si no se encuentra el usuario, destruir la sesión
        session_destroy();
        echo json_encode([
            'success' => false,
            'message' => 'Invalid session'
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'user' => $user,
        'session_id' => session_id() // Incluir el ID de sesión para debugging
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error'
    ]);
}
?>