<?php
// get_user.php
//header('Content-Type: application/json');

//try {
    //if (!isset($_GET['id'])) {
      //  throw new Exception('ID de usuario no proporcionado');
    //}

    //$dbconn = pg_connect("host=localhost port=5432 dbname=colegio user=Administrador password=colegio");
    
    //if (!$dbconn) {
      //  throw new Exception("Error en la conexión a la base de datos");
    //}

    //$userId = $_GET['id'];
    //$query = "SELECT id, nombre, tipo_usuario, email, telefono FROM usuarios WHERE id = $1";
    
    //$result = pg_query_params($dbconn, $query, array($userId));
    
    //if (!$result) {
      //  throw new Exception("Error al obtener datos del usuario: " . pg_last_error($dbconn));
    //}

    //$user = pg_fetch_assoc($result);
    
    //if (!$user) {
      //  throw new Exception("Usuario no encontrado");
    //}

    //echo json_encode([
      //  'success' => true,
    //    'user' => $user
  //  ]);

//} catch (Exception $e) {
    //echo json_encode([
      //  'success' => false,
    //    'error' => $e->getMessage()
  //  ]);
//}

//if (isset($dbconn)) {
  //  pg_close($dbconn);
//}
//?>
<?php
// get_users.php
header('Content-Type: application/json');

// Incluir la configuración de conexión desde config.php
include('config.php');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID de usuario no proporcionado');
    }

    // Usamos la función getDBConnection() para obtener la conexión a la base de datos de Railway
    $dbconn = getDBConnection();
    
    if (!$dbconn) {
        throw new Exception("Error en la conexión a la base de datos");
    }

    $userId = $_GET['id'];
    // Usamos un parámetro para evitar inyecciones SQL
    $query = "SELECT id, nombre, tipo_usuario, email, telefono FROM usuarios WHERE id = :id";
    
    // Preparamos la consulta
    $stmt = $dbconn->prepare($query);
    $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    
    // Obtenemos los resultados
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        throw new Exception("Usuario no encontrado");
    }

    echo json_encode([
        'success' => true,
        'user' => $user
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>
