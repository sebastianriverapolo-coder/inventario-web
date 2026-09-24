<?php
$servidor = "localhost";
$usuario_bd = "root";
$contrasena_bd = "";
$nombre_bd = "inventario_fontidueno";

$conexion = new mysqli($servidor, $usuario_bd, $contrasena_bd, $nombre_bd);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>