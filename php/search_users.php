<?php
header('Content-Type: application/json');

try {
    // Configuración de la conexión a PostgreSQL
    $dbconn = pg_connect("host=localhost port=5432 dbname=colegio user=Administrador password=colegio");
    
    if (!$dbconn) {
        throw new Exception("Error en la conexión a la base de datos");
    }

    // Obtener los parámetros de búsqueda
    $searchField = isset($_POST['searchField']) ? $_POST['searchField'] : 'all';
    $searchValue = isset($_POST['searchValue']) ? $_POST['searchValue'] : '';

    // Construir la consulta SQL base
    $query = "SELECT id, nombre, apellido_paterno, apellido_materno, ic, email, tipo_usuario, telefono FROM usuarios";

    // Modificar la consulta según los criterios de búsqueda
    if ($searchField !== 'all' && !empty($searchValue)) {
        switch ($searchField) {
            case 'id':
                $query .= " WHERE id = $1";
                $params = array($searchValue);
                break;
            case 'nombre':
                $query .= " WHERE LOWER(nombre) LIKE LOWER($1)";
                $params = array("%" . $searchValue . "%");
                break;
            case 'ic':
                $query .= " WHERE ic = $1";
                $params = array($searchValue);
                break;
            case 'email':
                $query .= " WHERE LOWER(email) LIKE LOWER($1)";
                $params = array("%" . $searchValue . "%");
                break;
            default:
                $params = array();
        }
    } else {
        $params = array();
    }

    // Ejecutar la consulta
    $result = $searchField !== 'all' && !empty($searchValue) 
        ? pg_query_params($dbconn, $query, $params)
        : pg_query($dbconn, $query);

    if (!$result) {
        throw new Exception("Error en la consulta: " . pg_last_error($dbconn));
    }

    // Obtener los resultados
    $users = array();
    while ($row = pg_fetch_assoc($result)) {
        $users[] = array(
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'apellido_paterno' => $row['apellido_paterno'],
            'apellido_materno' => $row['apellido_materno'],
            'ic' => $row['ic'],
            'email' => $row['email'],
            'tipo_usuario' => $row['tipo_usuario'],
            'telefono' => $row['telefono']
        );
    }

    // Devolver respuesta exitosa
    echo json_encode(array(
        'success' => true,
        'users' => $users
    ));

} catch (Exception $e) {
    // Devolver respuesta de error
    echo json_encode(array(
        'success' => false,
        'error' => $e->getMessage()
    ));
}

// Cerrar la conexión
if (isset($dbconn)) {
    pg_close($dbconn);
}
?>