<?php
session_start();
require_once("sistema/conexion.php");

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email    = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $usuario = $resultado->fetch_assoc();
        if (password_verify($password, $usuario['password'])) {
            $_SESSION['id']     = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['rol']    = $usuario['rol'];
            header("Location: panel/index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "No se encontró ninguna cuenta con ese correo.";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | Nexus RH</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="login-body">

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">Nexus <span>RH</span></div>
        <p class="login-sub">Ingresa tus credenciales para continuar</p>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-3" style="border-radius:9px;font-size:.86rem;">
            <i class="bi bi-exclamation-circle-fill"></i>
            <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label for="email">Correo electrónico</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="correo@nexusrh.com" required>
            </div>
            <div class="mb-4">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn-login-submit">Ingresar</button>
        </form>

        <div class="login-back">
            <a href="index.php"><i class="bi bi-arrow-left me-1"></i>Volver al inicio</a>
        </div>

    </div>
</div>

</body>
</html>
