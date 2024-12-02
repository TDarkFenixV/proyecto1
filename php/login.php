<?php
// login.php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$email = $data['email'] ?? '';
$password = $data['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email and password are required']);
    exit;
}

try {
    $pdo = getDBConnection();
    
    $stmt = $pdo->prepare("SELECT id, tipo_usuario, nombre, apellido_paterno, apellido_materno, 
                                 ic, email, sexo, telefono, password, fecha_registro 
                          FROM usuarios 
                          WHERE email = :email AND activo = true");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid email or password']);
        exit;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_type'] = $user['tipo_usuario'];
    $_SESSION['user_email'] = $user['email'];
    
    // Remove password from response
    unset($user['password']);
    
    // Determine redirect URL based on user type
    $redirectUrl = '';
    switch ($user['tipo_usuario']) {
        case 'student':
            $redirectUrl = 'estudiante.html';
            break;
        case 'teacher':
            $redirectUrl = 'profesor.html';
            break;
        case 'admin':
            $redirectUrl = 'admin.html';
            break;
        case 'director':
            $redirectUrl = 'director.html';
            break;
        default:
            $redirectUrl = 'index.html';
    }
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'redirect' => $redirectUrl
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error']);
}
?>