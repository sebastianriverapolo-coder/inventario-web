<?php
session_start();
include 'conexion.php';

if (!in_array($_SESSION['rol'] ?? null, ['profesor', 'admin'])) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje' => 'No autorizado']);
    exit();
}

$id_articulo = intval($_POST['id_articulo'] ?? 0);

if ($id_articulo <= 0) {
    echo json_encode(['exito' => false, 'mensaje' => 'ID inválido']);
    exit();
}

// Borramos en orden: características -> unidades -> artículo
$conexion->query("DELETE c FROM caracteristicas c INNER JOIN unidades u ON c.id_unidad = u.id_unidad WHERE u.id_articulo = $id_articulo");
$conexion->query("DELETE FROM unidades WHERE id_articulo = $id_articulo");
$conexion->query("DELETE FROM articulos WHERE id_articulo = $id_articulo");

echo json_encode(['exito' => true]);