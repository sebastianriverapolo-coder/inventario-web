<?php
session_start();
include 'conexion.php';

$id_articulo = intval($_GET['id_articulo'] ?? 0);

$consulta_articulo = $conexion->prepare("SELECT codigo, nombre, id_salon FROM articulos WHERE id_articulo = ?");
$consulta_articulo->bind_param("i", $id_articulo);
$consulta_articulo->execute();
$articulo = $consulta_articulo->get_result()->fetch_assoc();

$consulta_unidades = $conexion->prepare("SELECT id_unidad, codigo_unidad, estado, descripcion FROM unidades WHERE id_articulo = ? ORDER BY id_unidad");
$consulta_unidades->bind_param("i", $id_articulo);
$consulta_unidades->execute();
$unidades = $consulta_unidades->get_result()->fetch_all(MYSQLI_ASSOC);

$rol = $_SESSION['rol'] ?? null;
$id_salon = $articulo['id_salon'] ?? 0;
$url_volver = in_array($rol, ['profesor', 'admin']) ? "editar.php?id_salon=$id_salon" : "inventario.php?id_salon=$id_salon";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle del artículo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="detalle-container">
        <h1><?php echo htmlspecialchars($articulo['nombre'] ?? 'Artículo'); ?> (<?php echo htmlspecialchars($articulo['codigo'] ?? ''); ?>)</h1>

        <div class="search-container">
            <input type="text" id="buscar-ficha" placeholder="Buscar herramienta...">
        </div>

        <div class="fichas-scroll" id="fichas-scroll">
            <?php if (empty($unidades)): ?>
                <p style="text-align: center;">Este artículo todavía no tiene unidades registradas.</p>
            <?php endif; ?>

            <?php foreach ($unidades as $unidad): 
                $consulta_carac = $conexion->prepare("SELECT nombre_caracteristica, valor_caracteristica FROM caracteristicas WHERE id_unidad = ? ORDER BY orden");
                $consulta_carac->bind_param("i", $unidad['id_unidad']);
                $consulta_carac->execute();
                $caracteristicas = $consulta_carac->get_result()->fetch_all(MYSQLI_ASSOC);
            ?>
                <section class="ficha-articulo" data-id-unidad="<?php echo $unidad['id_unidad']; ?>">
                    <div class="ficha-encabezado">
                        <h2><?php echo htmlspecialchars($articulo['nombre']); ?></h2>
                        <span><?php echo htmlspecialchars($unidad['codigo_unidad']); ?></span>
                    </div>

                    <div class="ficha-vista" data-estado="<?php echo htmlspecialchars($unidad['estado']); ?>">
                        <div class="caracteristica">
                            <span class="nombre-caracteristica">Estado</span>
                            <span class="valor-caracteristica"><?php echo htmlspecialchars($unidad['estado']); ?></span>
                        </div>

                        <?php if (!empty($unidad['descripcion'])): ?>
                            <div class="caracteristica">
                                <span class="nombre-caracteristica">Descripción</span>
                                <span class="valor-caracteristica valor-descripcion-actual"><?php echo nl2br(htmlspecialchars($unidad['descripcion'])); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php foreach ($caracteristicas as $carac): ?>
                            <div class="caracteristica" data-nombre="<?php echo htmlspecialchars($carac['nombre_caracteristica']); ?>" data-valor="<?php echo htmlspecialchars($carac['valor_caracteristica']); ?>">
                                <span class="nombre-caracteristica"><?php echo htmlspecialchars($carac['nombre_caracteristica']); ?></span>
                                <span class="valor-caracteristica"><?php echo htmlspecialchars($carac['valor_caracteristica']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <?php if (in_array($rol, ['profesor', 'admin'])): ?>
                        <div class="ficha-acciones-individual">
                            <button class="btn-editar-unidad">Editar</button>
                            <button class="btn-eliminar-unidad">Eliminar</button>
                        </div>
                    <?php endif; ?>
                </section>
            <?php endforeach; ?>

        </div>

        <a href="<?php echo $url_volver; ?>" class="btn-back">Volver al inventario</a>
    </main>

    <?php if (in_array($rol, ['profesor', 'admin'])): ?>

    <!-- Formulario para agregar una ficha nueva, oculto hasta que se necesite -->
    <div class="ficha-articulo" id="formulario-nueva-ficha" style="display: none;">
        <div class="ficha-encabezado">
            <h2>Nueva unidad</h2>
        </div>

        <div class="caracteristica">
            <span class="nombre-caracteristica">Estado</span>
            <select id="select-estado" required>
                <option value="" disabled selected>Selecciona un estado</option>
                <option value="Bueno">Bueno</option>
                <option value="Dañado">Dañado</option>
                <option value="Mantenimiento">Mantenimiento</option>
                <option value="Prestado">Prestado</option>
            </select>
        </div>

        <div class="caracteristica" id="contenedor-descripcion" style="display: none;">
          <span class="nombre-caracteristica">Descripción</span>
          <textarea id="input-descripcion" placeholder="Descripción del elemento"></textarea>
        </div>

        <div id="caracteristicas-nuevas"></div>

        <div class="ficha-acciones-individual">
            <button type="button" id="btn-agregar-caracteristica">+ Agregar característica</button>
            <button type="button" id="btn-guardar-ficha">Guardar</button>
            <button type="button" id="btn-cancelar-ficha">Cancelar</button>
        </div>
    </div>

    <div class="barra-acciones-fija">
        <button id="btn-agregar-ficha">+ Agregar ficha</button>
    </div>
<?php endif; ?>

        <div id="modal-confirmacion" class="modal-overlay" style="display: none;">
        <div class="modal-caja">
            <p id="modal-mensaje"></p>
            <div class="modal-botones">
                <button id="modal-btn-si">Sí</button>
                <button id="modal-btn-no">No</button>
            </div>
        </div>
    </div>

    <script>
        const idArticuloActual = <?php echo $id_articulo; ?>;
    </script>
    <script src="script.js"></script>
</body>
</html>