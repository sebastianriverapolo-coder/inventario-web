<?php
session_start();
include 'conexion.php';

if (($_SESSION['rol'] ?? null) !== 'admin') {
    header("Location: salones.php");
    exit();
}

$mensaje = "";

// Crear un nuevo profesor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'crear') {
    $correo = trim($_POST['correo'] ?? '');
    $contrasena = trim($_POST['contrasena'] ?? '');

    if ($correo === '' || $contrasena === '') {
        $mensaje = "Usuario y contraseña son obligatorios.";
    } elseif (strlen($contrasena) < 8) {
        $mensaje = "La contraseña debe tener al menos 8 caracteres.";
    } else {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $insertar = $conexion->prepare("INSERT INTO usuarios (nombre, correo, contrasena, rol) VALUES (?, ?, ?, 'profesor')");
        $insertar->bind_param("sss", $correo, $correo, $hash);

        try {
            $insertar->execute();
            $mensaje = "Profesor creado correctamente.";
        } catch (mysqli_sql_exception $e) {
            $mensaje = "Ese usuario ya está registrado.";
        }
    }
}

// Restablecer la contraseña de un profesor existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'restablecer') {
    $id_usuario = intval($_POST['id_usuario'] ?? 0);
    $contrasena_nueva = trim($_POST['contrasena_nueva'] ?? '');

    if (strlen($contrasena_nueva) < 8) {
        $mensaje = "La nueva contraseña debe tener al menos 8 caracteres.";
    } else {
        $hash = password_hash($contrasena_nueva, PASSWORD_DEFAULT);
        $actualizar = $conexion->prepare("UPDATE usuarios SET contrasena = ? WHERE id_usuario = ? AND rol != 'admin'");
        $actualizar->bind_param("si", $hash, $id_usuario);
        $actualizar->execute();
        $mensaje = "Contraseña actualizada correctamente.";
    }
}

$usuarios = $conexion->query("SELECT id_usuario, correo, rol FROM usuarios ORDER BY rol DESC, correo")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Inventario IE Fontidueño</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="container">
        <header class="header-colegio">
            <h1>IE FONTIDUEÑO JAIME ARANGO ROJAS</h1>
            <img src="imagenes/escudo.png" alt="Escudo del colegio" class="escudo">
        </header>

        <h2 class="room-title">Gestión de usuarios</h2>

        <?php if ($mensaje): ?>
            <p style="text-align: center; color: #ffcccc;"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <form method="POST" class="login-form" style="margin-bottom: 30px;">
            <input type="hidden" name="accion" value="crear">
            <h2>Nuevo profesor</h2>
            <div class="form-group">
                <label>Usuario:</label>
                <input type="text" name="correo" required>
            </div>
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="contrasena" minlength="8" required>
            </div>
            <button type="submit" class="btn-submit">Crear profesor</button>
        </form>

        <div class="table-container">
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-usuarios">
                    <?php foreach ($usuarios as $usuario): ?>
                        <tr data-id-usuario="<?php echo $usuario['id_usuario']; ?>">
                            <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                            <td><?php echo htmlspecialchars($usuario['rol']); ?></td>
                            <td>
                                <?php if ($usuario['rol'] !== 'admin'): ?>
                                    <button class="btn-restablecer-clave">Restablecer contraseña</button>
                                    <button class="btn-eliminar-usuario">Eliminar</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <a href="salones.php" class="btn-back">Volver a Salones</a>
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
    <div id="modal-restablecer" class="modal-overlay" style="display: none;">
    <div class="modal-caja">
        <p>Nueva contraseña (mínimo 8 caracteres):</p>
        <input type="password" id="modal-input-contrasena" minlength="8">
        <div class="modal-botones">
            <button id="modal-btn-restablecer-confirmar">Guardar</button>
            <button id="modal-btn-restablecer-cancelar">Cancelar</button>
        </div>
    </div>
</div>

    <script src="script.js"></script>
</body>
</html>