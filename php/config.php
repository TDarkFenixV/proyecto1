<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432');
define('DB_NAME', 'colegio');
define('DB_USER', 'Administrador');
define('DB_PASSWORD', 'colegio');

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