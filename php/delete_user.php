<?php
// delete_user.php
header('Content-Type: application/json');

try {
    // Verificar si se recibió el ID
    if (!isset($_POST['id'])) {
        throw new Exception('ID de usuario no proporcionado');
    }

    $userId = $_POST['id'];
    
    // Validar que el ID sea un número
    if (!is_numeric($userId)) {
        throw new Exception('ID de usuario no válido');
    }

    // Conectar a la base de datos
    $dbconn = pg_connect("host=localhost dbname=colegio user=Administrador password=colegio");
    
    if (!$dbconn) {
        throw new Exception('Error de conexión a la base de datos');
    }

    // Comenzar transacción
    pg_query($dbconn, "BEGIN");

    // Preparar la consulta
    $query = "DELETE FROM usuarios WHERE id = $1 RETURNING id";
    
    // Ejecutar la consulta
    $result = pg_query_params($dbconn, $query, array($userId));

    if (!$result) {
        throw new Exception(pg_last_error($dbconn));
    }

    // Verificar si se eliminó algún registro
    if (pg_affected_rows($result) == 0) {
        throw new Exception('No se encontró el usuario especificado');
    }

    // Confirmar transacción
    pg_query($dbconn, "COMMIT");

    // Cerrar la conexión
    pg_close($dbconn);

    // Enviar respuesta exitosa
    echo json_encode([
        'success' => true,
        'message' => 'Usuario eliminado correctamente'
    ]);

} catch (Exception $e) {
    // Si hay error, hacer rollback
    if (isset($dbconn)) {
        pg_query($dbconn, "ROLLBACK");
        pg_close($dbconn);
    }

    // Enviar respuesta de error
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>