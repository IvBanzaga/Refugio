<?php
/* TODO: Conexión segura a la base de datos usando PDO con MySQL.
   Se activan los errores y excepciones para facilitar la depuración con Xdebug.
   Depuración: puedes poner breakpoint en el try/catch para comprobar la conexión y los errores.
   Las variables de conexión se deben cambiar según el entorno. */

$host     = "localhost";
$port     = "3306";    // Puerto por defecto de MySQL
$dbname   = "refugio"; // Nombre de la base de datos
$username = "root";    // Usuario con permisos
$password = "123456";  // Contraseña

try {
    $conexionPDO = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $conexionPDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexionPDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // Configurar el modo de MySQL para mejor compatibilidad
    $conexionPDO->exec("SET sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''))");
    session_start();
} catch (PDOException $e) {
    die("La conexion con la base de datos $dbname ha fallado: " . $e->getMessage());
}
