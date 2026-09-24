<?php
session_start();
include 'conexion.php';

if (!in_array($_SESSION['rol'] ?? null, ['profesor', 'admin'])) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$id_articulo = intval($_POST['id_articulo'] ?? 0);
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');

if ($id_articulo <= 0 || $codigo === '' || $nombre === '') {
    echo json_encode(['exito' => false, 'mensaje' => 'Código y nombre son obligatorios']);
    exit();
}

$actualizar = $conexion->prepare("UPDATE articulos SET codigo = ?, nombre = ? WHERE id_articulo = ?");
$actualizar->bind_param("ssi", $codigo, $nombre, $id_articulo);
$actualizar->execute();

echo json_encode(['exito' => true]);