<?php
session_start();
include 'conexion.php';

if (!in_array($_SESSION['rol'] ?? null, ['profesor', 'admin'])) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$id_unidad = intval($_POST['id_unidad'] ?? 0);

if ($id_unidad <= 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'ID inválido']);
    exit();
}

// Primero borramos las características (dependen de la unidad)
$eliminar_carac = $conexion->prepare("DELETE FROM caracteristicas WHERE id_unidad = ?");
$eliminar_carac->bind_param("i", $id_unidad);
$eliminar_carac->execute();

// Ahora sí podemos borrar la unidad, sin que queden características huérfanas
$eliminar_unidad = $conexion->prepare("DELETE FROM unidades WHERE id_unidad = ?");
$eliminar_unidad->bind_param("i", $id_unidad);
$eliminar_unidad->execute();

echo json_encode(['exito' => true]);