<?php
session_start();
include 'conexion.php';

if (!in_array($_SESSION['rol'] ?? null, ['profesor', 'admin'])) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$id_salon = intval($_POST['id_salon'] ?? 0);
$codigo = trim($_POST['codigo'] ?? '');
$nombre = trim($_POST['nombre'] ?? '');

if ($id_salon <= 0 || $codigo === '' || $nombre === '') {
    echo json_encode(['exito' => false, 'mensaje' => 'Código y nombre son obligatorios']);
    exit();
}

$insertar = $conexion->prepare("INSERT INTO articulos (id_salon, codigo, nombre) VALUES (?, ?, ?)");
$insertar->bind_param("iss", $id_salon, $codigo, $nombre);
$insertar->execute();

echo json_encode(['exito' => true]);