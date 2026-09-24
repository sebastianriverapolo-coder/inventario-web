<?php
session_start();
include 'conexion.php';

if (!in_array($_SESSION['rol'] ?? null, ['profesor', 'admin'])) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$id_unidad = intval($_POST['id_unidad'] ?? 0);
$estado = trim($_POST['estado'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$nombres_carac = $_POST['carac_nombre'] ?? [];
$valores_carac = $_POST['carac_valor'] ?? [];

$estados_validos = ['Bueno', 'Dañado', 'Mantenimiento', 'Prestado'];
if ($id_unidad <= 0 || !in_array($estado, $estados_validos)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Debes seleccionar un estado válido']);
    exit();
}

if ($estado === 'Prestado' && $descripcion === '') {
    echo json_encode(['exito' => false, 'mensaje' => 'Debes indicar una descripción cuando el estado es Prestado']);
    exit();
}

// Actualizamos el estado y la descripción de la unidad
$actualizar = $conexion->prepare("UPDATE unidades SET estado = ?, descripcion = ? WHERE id_unidad = ?");
$actualizar->bind_param("ssi", $estado, $descripcion, $id_unidad);
$actualizar->execute();

// Reemplazamos todas las características: borramos las viejas, metemos las nuevas
$borrar = $conexion->prepare("DELETE FROM caracteristicas WHERE id_unidad = ?");
$borrar->bind_param("i", $id_unidad);
$borrar->execute();

$insertar = $conexion->prepare("INSERT INTO caracteristicas (id_unidad, nombre_caracteristica, valor_caracteristica, orden) VALUES (?, ?, ?, ?)");
foreach ($nombres_carac as $i => $nombre) {
    $nombre = trim($nombre);
    $valor = trim($valores_carac[$i] ?? '');
    if ($nombre === '' || $valor === '') continue;
    $orden = $i;
    $insertar->bind_param("issi", $id_unidad, $nombre, $valor, $orden);
    $insertar->execute();
}

echo json_encode(['exito' => true]);