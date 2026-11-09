<?php
$host = "localhost";
$user = "adm_webgenerator";
$pass = "webgenerator2024";
$db   = "webgenerator";
try {
    $conexion = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión a la base de datos.");
}
