<?php
session_start();
include 'conexion.php';

$id_salon = intval($_GET['id_salon'] ?? 0);

$consulta_salon = $conexion->prepare("SELECT nombre FROM salones WHERE id_salon = ?");
$consulta_salon->bind_param("i", $id_salon);
$consulta_salon->execute();
$salon = $consulta_salon->get_result()->fetch_assoc();
$nombre_salon = $salon['nombre'] ?? 'Salón desconocido';

$consulta_articulos = $conexion->prepare("SELECT id_articulo, codigo, nombre FROM articulos WHERE id_salon = ?");
$consulta_articulos->bind_param("i", $id_salon);
$consulta_articulos->execute();
$articulos = $consulta_articulos->get_result()->fetch_all(MYSQLI_ASSOC);

foreach ($articulos as &$articulo) {
    $consulta_unidades = $conexion->prepare("SELECT estado, COUNT(*) AS cantidad FROM unidades WHERE id_articulo = ? GROUP BY estado");
    $consulta_unidades->bind_param("i", $articulo['id_articulo']);
    $consulta_unidades->execute();
    $filas_estado = $consulta_unidades->get_result()->fetch_all(MYSQLI_ASSOC);

    $conteos = ['Bueno' => 0, 'Dañado' => 0, 'Mantenimiento' => 0, 'Prestado' => 0];
    foreach ($filas_estado as $fila) {
        if (isset($conteos[$fila['estado']])) {
            $conteos[$fila['estado']] = (int)$fila['cantidad'];
        }
    }

    $articulo['cantidad_total'] = array_sum($conteos);
    $articulo['conteo_bueno'] = $conteos['Bueno'];
    $articulo['conteo_danado'] = $conteos['Dañado'];
    $articulo['conteo_mantenimiento'] = $conteos['Mantenimiento'];
    $articulo['conteo_prestado'] = $conteos['Prestado'];

    $partes = [];
    foreach ($conteos as $nombre_estado => $cantidad) {
        if ($cantidad > 0) $partes[] = "$cantidad $nombre_estado";
    }
    $articulo['estado_resumen'] = $partes ? implode(", ", $partes) : "Sin unidades";
}
unset($articulo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - IE Fontidueño</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="container">
        <header class="header-colegio">
            <h1>IE FONTIDUEÑO JAIME ARANGO ROJAS</h1>
            <img src="imagenes/escudo.png" alt="Escudo del colegio" class="escudo">
        </header>

        <h2 class="room-title">Inventario actual: <?php echo htmlspecialchars($nombre_salon); ?></h2>

        <div class="search-container">
            <input type="text" id="busqueda" placeholder="Buscar herramienta...">
        </div>

        <div class="filtros-estado">
            <button class="btn-filtro" data-filtro="todos">Todos</button>
            <button class="btn-filtro" data-filtro="bueno">Buenos</button>
            <button class="btn-filtro" data-filtro="danado">Dañados</button>
            <button class="btn-filtro" data-filtro="mantenimiento">En mantenimiento</button>
            <button class="btn-filtro" data-filtro="prestado">Prestados</button>
        </div>

        <div class="table-container">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Elemento</th>
                        <th>Cantidad</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody id="tabla-contenido">
                    <?php foreach ($articulos as $articulo): ?>
                        <tr 
                            data-id-articulo="<?php echo $articulo['id_articulo']; ?>"
                            data-bueno="<?php echo $articulo['conteo_bueno']; ?>"
                            data-danado="<?php echo $articulo['conteo_danado']; ?>"
                            data-mantenimiento="<?php echo $articulo['conteo_mantenimiento']; ?>"
                            data-prestado="<?php echo $articulo['conteo_prestado']; ?>"
                            data-resumen="<?php echo htmlspecialchars($articulo['estado_resumen']); ?>"
                        >
                            <td><?php echo htmlspecialchars($articulo['nombre']); ?></td>
                            <td><?php echo $articulo['cantidad_total']; ?></td>
                            <td class="celda-estado"><?php echo htmlspecialchars($articulo['estado_resumen']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="salones.php" class="btn-back">Volver a Salones</a>
    </main>

    <script src="script.js"></script>
</body>
</html>