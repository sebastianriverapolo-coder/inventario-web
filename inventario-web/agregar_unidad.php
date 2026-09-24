<?php
session_start();
include 'conexion.php';

if (!in_array($_SESSION['rol'] ?? null, ['profesor', 'admin'])) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$id_articulo = intval($_POST['id_articulo'] ?? 0);
$estado = trim($_POST['estado'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$nombres_carac = $_POST['carac_nombre'] ?? [];
$valores_carac = $_POST['carac_valor'] ?? [];

$estados_validos = ['Bueno', 'Dañado', 'Mantenimiento', 'Prestado'];
if ($id_articulo <= 0 || !in_array($estado, $estados_validos)) {
    echo json_encode(['exito' => false, 'mensaje' => 'Debes seleccionar un estado válido']);
    exit();
}

if ($estado === 'Prestado' && $descripcion === '') {
    echo json_encode(['exito' => false, 'mensaje' => 'Debes indicar una descripción cuando el estado es Prestado']);
    exit();
}

$consulta = $conexion->prepare("SELECT codigo FROM articulos WHERE id_articulo = ?");
$consulta->bind_param("i", $id_articulo);
$consulta->execute();
$articulo = $consulta->get_result()->fetch_assoc();

$consulta_conteo = $conexion->prepare("SELECT COUNT(*) AS total FROM unidades WHERE id_articulo = ?");
$consulta_conteo->bind_param("i", $id_articulo);
$consulta_conteo->execute();
$conteo = $consulta_conteo->get_result()->fetch_assoc();

$siguiente_numero = $conteo['total'] + 1;
$codigo_unidad = $articulo['codigo'] . "-" . str_pad($siguiente_numero, 2, "0", STR_PAD_LEFT);

$insertar_unidad = $conexion->prepare("INSERT INTO unidades (id_articulo, codigo_unidad, estado, descripcion) VALUES (?, ?, ?, ?)");
$insertar_unidad->bind_param("isss", $id_articulo, $codigo_unidad, $estado, $descripcion);
$insertar_unidad->execute();

$id_unidad_nueva = $conexion->insert_id;

$insertar_carac = $conexion->prepare("INSERT INTO caracteristicas (id_unidad, nombre_caracteristica, valor_caracteristica, orden) VALUES (?, ?, ?, ?)");
foreach ($nombres_carac as $i => $nombre) {
    $nombre = trim($nombre);
    $valor = trim($valores_carac[$i] ?? '');
    if ($nombre === '' || $valor === '') continue;
    $orden = $i;
    $insertar_carac->bind_param("issi", $id_unidad_nueva, $nombre, $valor, $orden);
    $insertar_carac->execute();
}

echo json_encode(['exito' => true]);