<?php
session_start();
include 'conexion.php';

// Traemos los salones directamente de la base de datos, no fijos en el HTML
$resultado = $conexion->query("SELECT id_salon, nombre FROM salones ORDER BY id_salon");

// Decidimos a qué página enviar según el rol guardado en la sesión
$rol = $_SESSION['rol'] ?? null; // el ?? significa: si no existe, usa null en vez de dar error
$destino = in_array($rol, ['profesor', 'admin']) ? 'editar.php' : 'inventario.php';

// Como la base de datos no guarda imágenes por salón, las relacionamos aquí por nombre
$imagenes = [
    'Sala de Sistemas' => 'imagenes/sala-sistemas.jpg',
    'Sala de Informática' => 'imagenes/sala-informatica.png',
    'Escuelita' => 'imagenes/escudo.png'
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salones - Inventario IE Fontidueño</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="container">
        <header class="header-colegio">
    <h1>IE FONTIDUEÑO JAIME ARANGO ROJAS</h1>
    <img src="imagenes/escudo.png" alt="Escudo del colegio" class="escudo">

      <?php if (($_SESSION['rol'] ?? null) === 'admin'): ?>
        <a href="usuarios.php" class="btn-gestion-usuarios">Gestión de usuarios</a>
      <?php endif; ?>
        </header>

        <section class="rooms-container">

            <?php while ($salon = $resultado->fetch_assoc()): ?>
                <a class="room-card" href="<?php echo $destino; ?>?id_salon=<?php echo $salon['id_salon']; ?>">
                    <h3><?php echo htmlspecialchars($salon['nombre']); ?></h3>
                    <div class="image-wrapper">
                        <img src="<?php echo $imagenes[$salon['nombre']] ?? 'imagenes/escudo.png'; ?>" alt="<?php echo htmlspecialchars($salon['nombre']); ?>">
                    </div>
                </a>
            <?php endwhile; ?>

        </section>
        
                <div class="volver-inicio-centrado">
            <?php if (in_array($rol, ['profesor', 'admin'])): ?>
                <a href="logout.php" class="btn-back" id="btn-volver-inicio">Volver al inicio</a>
            <?php else: ?>
                <a href="index.html" class="btn-back">Volver al inicio</a>
            <?php endif; ?>
        </div>
    </main>

    <div id="modal-confirmacion" class="modal-overlay" style="display: none;">
        <div class="modal-caja">
            <p id="modal-mensaje"></p>
            <div class="modal-botones">
                <button id="modal-btn-si">Sí</button>
                <button id="modal-btn-no">No</button>
            </div>
        </div>
    </div>

    <script src="script.js"></script>
</body>
</html>