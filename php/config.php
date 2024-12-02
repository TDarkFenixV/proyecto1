<?php
// config.php
//define('DB_HOST', 'localhost');
//define('DB_PORT', '5432');
//define('DB_NAME', 'colegio');
//define('DB_USER', 'Administrador');
//define('DB_PASSWORD', 'colegio');
define('DB_HOST', 'autorack.proxy.rlwy.net'); // Host de Railway
define('DB_PORT', '53133'); // Puerto de Railway
define('DB_NAME', 'railway'); // Nombre de la base de datos en Railway
define('DB_USER', 'postgres'); // Usuario de Railway
define('DB_PASSWORD', 'gKIBUxQirHbFNfZmfVnIBtLvdVRYsyBK'); // Contraseña de Railway

function getDBConnection() {
    try {
        $dsn = "pgsql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";";
        $pdo = new PDO($dsn, DB_USER, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
