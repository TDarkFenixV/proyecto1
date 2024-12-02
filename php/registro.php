<?php
session_start();
require_once 'config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

try {
    $pdo = getDBConnection();
    $pdo->beginTransaction(); // Iniciamos una transacción

    // Validar datos requeridos
    $requiredFields = ['tipo_usuario', 'nombre', 'apellido_paterno', 'apellido_materno', 
                      'ic', 'email', 'sexo', 'telefono', 'password', 'fecha_nacimiento'];
    
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("El campo $field es requerido");
        }
    }

    // Validar que el email y IC no existan
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? OR ic = ?");
    $stmt->execute([$_POST['email'], $_POST['ic']]);
    if ($stmt->fetchColumn() > 0) {
        throw new Exception("El email o IC ya están registrados");
    }

    // Validar tipo de usuario
    $tiposValidos = ['student', 'teacher', 'admin', 'director'];
    if (!in_array($_POST['tipo_usuario'], $tiposValidos)) {
        throw new Exception("Tipo de usuario no válido");
    }

    // Validar sexo
    $sexosValidos = ['hombre', 'mujer', 'otro'];
    if (!in_array($_POST['sexo'], $sexosValidos)) {
        throw new Exception("Sexo no válido");
    }

    // Preparar y ejecutar la consulta de inserción de usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios (
        tipo_usuario, nombre, apellido_paterno, apellido_materno, 
        ic, email, sexo, telefono, password, fecha_nacimiento
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id");

    $stmt->execute([
        $_POST['tipo_usuario'],
        $_POST['nombre'],
        $_POST['apellido_paterno'],
        $_POST['apellido_materno'],
        $_POST['ic'],
        $_POST['email'],
        $_POST['sexo'],
        $_POST['telefono'],
        password_hash($_POST['password'], PASSWORD_DEFAULT),
        $_POST['fecha_nacimiento']
    ]);

    $usuario_id = $stmt->fetchColumn();

    // Si es estudiante, calcular edad e inscribir en materias
    if ($_POST['tipo_usuario'] === 'student') {
        // Calcular edad
        $fechaNacimiento = new DateTime($_POST['fecha_nacimiento']);
        $hoy = new DateTime();
        $edad = $hoy->diff($fechaNacimiento)->y;

        // Obtener materias según la edad
        $sqlMaterias = "SELECT id FROM materias WHERE activo = true AND ";
        if ($edad < 15) {
            $sqlMaterias .= "nivel = 'basico'";
        } elseif ($edad >= 15 && $edad < 18) {
            $sqlMaterias .= "nivel = 'intermedio'";
        } else {
            $sqlMaterias .= "nivel = 'avanzado'";
        }

        $materias = $pdo->query($sqlMaterias)->fetchAll(PDO::FETCH_COLUMN);
        $periodoActual = date('Y');

        // Inscribir en las materias con asignación de paralelo
        if (!empty($materias)) {
            // Función para determinar el paralelo menos poblado para una materia
            function obtenerParaleloOptimo($pdo, $materia_id, $periodo_academico) {
                $sql = "SELECT paralelo, COUNT(*) as total 
                       FROM inscripciones 
                       WHERE materia_id = ? 
                       AND periodo_academico = ? 
                       GROUP BY paralelo 
                       ORDER BY total ASC";
                
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$materia_id, $periodo_academico]);
                $distribucion = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
                
                // Paralelos disponibles
                $paralelos = ['A', 'B', 'C'];
                
                // Si no hay estudiantes, asignar al primer paralelo
                if (empty($distribucion)) {
                    return 'A';
                }
                
                // Encontrar el paralelo con menos estudiantes
                foreach ($paralelos as $paralelo) {
                    if (!isset($distribucion[$paralelo])) {
                        return $paralelo; // Usar primer paralelo vacío encontrado
                    }
                }
                
                // Si todos los paralelos tienen estudiantes, usar el que tenga menos
                return array_search(min($distribucion), $distribucion);
            }

            $sqlInscripcion = "INSERT INTO inscripciones (
                estudiante_id, materia_id, periodo_academico, estado, fecha_registro, paralelo
            ) VALUES (?, ?, ?, 'activo', CURRENT_TIMESTAMP, ?)";
            
            $stmtInscripcion = $pdo->prepare($sqlInscripcion);

            foreach ($materias as $materia_id) {
                $paralelo = obtenerParaleloOptimo($pdo, $materia_id, $periodoActual);
                $stmtInscripcion->execute([
                    $usuario_id,
                    $materia_id,
                    $periodoActual,
                    $paralelo
                ]);
            }

            $mensaje = 'Usuario registrado exitosamente e inscrito en ' . count($materias) . ' materias';
        } else {
            $mensaje = 'Usuario registrado exitosamente, pero no se encontraron materias disponibles para su edad';
        }
    } else {
        $mensaje = 'Usuario registrado exitosamente';
    }

    // Confirmar la transacción
    $pdo->commit();

    echo json_encode([
        'success' => true,
        'message' => $mensaje,
        'usuario_id' => $usuario_id
    ]);

} catch (Exception $e) {
    // Revertir la transacción en caso de error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    http_response_code(400);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}
?>