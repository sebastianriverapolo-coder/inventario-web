<?php
session_start(); // Inicia el sistema de sesiones de PHP
include 'conexion.php'; // Trae la conexión a la base de datos

$mensaje_error = "";

// Esto se ejecuta SOLO si el formulario fue enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Buscamos si existe un usuario con ese correo
    $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $consulta->bind_param("s", $correo);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();

        // Verificamos la contraseña contra el código encriptado guardado
        if (password_verify($contrasena, $usuario['contrasena'])) {
            // Login correcto: guardamos datos en la sesión
            $_SESSION['rol'] = $usuario['rol'];
            $_SESSION['nombre'] = $usuario['nombre'];
            header("Location: salones.php");
            exit();
        } else {
            $mensaje_error = "Usuario o contraseña incorrectos.";
        }
    } else {
        $mensaje_error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Inventario IE Fontidueño</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <main class="container">
        <header class="header-colegio">
            <h1>IE FONTIDUEÑO JAIME ARANGO ROJAS</h1>
            <img src="imagenes/escudo.png" alt="Escudo del colegio" class="escudo">
        </header>

        <form class="login-form" method="POST" action="login.php">
            <h2>Acceso Profesores</h2>
            
            <div class="form-group">
               <label for="correo">Usuario:</label>
               <input type="text" id="correo" name="correo" required>
            </div>

            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="Ingrese su contraseña" required>
            </div>

            <button type="submit" class="btn-submit">Iniciar Sesión</button>
            
            <?php if ($mensaje_error): ?>
                <p style="color: #ffcccc; text-align: center; margin-top: 10px;"><?php echo $mensaje_error; ?></p>
            <?php endif; ?>
            
            <a href="index.html" class="btn-back">Volver al inicio</a>
        </form>
    </main>

</body>
</html>