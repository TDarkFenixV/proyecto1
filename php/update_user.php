<?php
// update_user.php
header('Content-Type: application/json');

try {
    if (!isset($_POST['id'])) {
        throw new Exception('ID de usuario no proporcionado');
    }

    $dbconn = pg_connect("host=localhost port=5432 dbname=colegio user=Administrador password=colegio");
    
    if (!$dbconn) {
        throw new Exception("Error en la conexión a la base de datos");
    }

    // Obtener los datos del formulario
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $tipo_usuario = $_POST['tipo_usuario'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];

    // Validar datos
    if (empty($nombre) || empty($tipo_usuario) || empty($email)) {
        throw new Exception("Todos los campos son obligatorios");
    }

    // Actualizar usuario
    $query = "UPDATE usuarios SET 
              nombre = $1,
              tipo_usuario = $2,
              email = $3,
              telefono = $4
              WHERE id = $5";
    
    $result = pg_query_params($dbconn, $query, array(
        $nombre,
        $tipo_usuario,
        $email,
        $telefono,
        $id
    ));
    
    if (!$result) {
        throw new Exception("Error al actualizar usuario: " . pg_last_error($dbconn));
    }

    echo json_encode([
        'success' => true,
        'message' => 'Usuario actualizado correctamente'
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

if (isset($dbconn)) {
    pg_close($dbconn);
}
?>