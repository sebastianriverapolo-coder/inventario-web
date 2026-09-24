<?php
session_start();
include 'conexion.php';

if (($_SESSION['rol'] ?? null) !== 'admin') {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$id_usuario = intval($_POST['id_usuario'] ?? 0);

// Protección extra: nunca permitir borrar a un admin, aunque intenten forzarlo
$consulta = $conexion->prepare("SELECT rol FROM usuarios WHERE id_usuario = ?");
$consulta->bind_param("i", $id_usuario);
$consulta->execute();
$usuario = $consulta->get_result()->fetch_assoc();

if (!$usuario || $usuario['rol'] === 'admin') {
    echo json_encode(['exito' => false, 'mensaje' => 'No se puede eliminar este usuario']);
    exit();
}

$eliminar = $conexion->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
$eliminar->bind_param("i", $id_usuario);
$eliminar->execute();

echo json_encode(['exito' => true]);