<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No hay sesión activa'
    ]);
    exit;
}

try {
    $pdo = getDBConnection();
    
    // Obtener la información del estudiante
    $stmt = $pdo->prepare("
        SELECT fecha_nacimiento 
        FROM usuarios 
        WHERE id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Calcular edad
    $fechaNacimiento = new DateTime($usuario['fecha_nacimiento']);
    $hoy = new DateTime();
    $edad = $hoy->diff($fechaNacimiento)->y;
    
    // Determinar nivel basado en la edad
    $nivel = '';
    if ($edad < 15) {
        $nivel = 'basico';
    } elseif ($edad >= 15 && $edad < 18) {
        $nivel = 'intermedio';
    } else {
        $nivel = 'avanzado';
    }
    
    // Obtener las materias del estudiante con su paralelo
    $stmt = $pdo->prepare("
        SELECT m.*, i.paralelo, i.estado
        FROM materias m
        INNER JOIN inscripciones i ON m.id = i.materia_id
        WHERE i.estudiante_id = ? 
        AND i.periodo_academico = ?
        ORDER BY m.nombre
    ");
    
    $periodoActual = date('Y');
    $stmt->execute([$_SESSION['user_id'], $periodoActual]);
    $materias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode([
        'success' => true,
        'data' => $materias,
        'nivel' => $nivel,
        'edad' => $edad
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>