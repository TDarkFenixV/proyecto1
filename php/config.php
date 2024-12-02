<?php
// config.php
//define('DB_HOST', 'localhost');
//define('DB_PORT', '5432');
//define('DB_NAME', 'colegio');
//define('DB_USER', 'Administrador');
//define('DB_PASSWORD', 'colegio');
//define('DB_HOST', 'autorack.proxy.rlwy.net'); // Host de Railway
//define('DB_PORT', '53133'); // Puerto de Railway
//define('DB_NAME', 'railway'); // Nombre de la base de datos en Railway
//define('DB_USER', 'postgres'); // Usuario de Railway
//define('DB_PASSWORD', 'gKIBUxQirHbFNfZmfVnIBtLvdVRYsyBK'); // Contraseña de Railway

//function getDBConnection() {
  //  try {
    //    $dsn = "pgsql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";";
      //  $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        //$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //return $pdo;
    //} catch(PDOException $e) {
      //  die("Connection failed: " . $e->getMessage());
    //}
//}

// config.php

// Usamos getenv para obtener la URL de la base de datos desde las variables de entorno
$DATABASE_URL = getenv('DATABASE_URL'); // Se espera que esta variable esté configurada en Vercel

// Si la variable de entorno DATABASE_URL no está configurada, podemos usar los valores fijos (aunque es mejor evitar esto en producción)
if (!$DATABASE_URL) {
    define('DB_HOST', 'autorack.proxy.rlwy.net');
    define('DB_PORT', '53133');
    define('DB_NAME', 'railway');
    define('DB_USER', 'postgres');
    define('DB_PASSWORD', 'gKIBUxQirHbFNfZmfVnIBtLvdVRYsyBK');
} else {
    // Si DATABASE_URL está configurado, se usa para establecer la conexión
    $url = parse_url($DATABASE_URL);
    define('DB_HOST', $url['host']);
    define('DB_PORT', $url['port']);
    define('DB_NAME', ltrim($url['path'], '/'));
    define('DB_USER', $url['user']);
    define('DB_PASSWORD', $url['pass']);
}

function getDBConnection() {
    try {
        // Construimos la cadena DSN para PostgreSQL
        $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";";
        // Creamos la conexión PDO con los parámetros de la base de datos
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

?>
