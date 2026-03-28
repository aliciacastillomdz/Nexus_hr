<?php
require_once("../sistema/conexion.php");

// Solo el admin puede registrar usuarios
if ($_SESSION['rol'] !== 'admin') {
    header("Location: index.php?page=dashboard");
    exit();
}

$mensaje = "";
$tipo    = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre   = trim($_POST['nombre']);
    $email    = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol      = $_POST['rol'] ?? 'usuario';

    // Verificar si el email ya existe
    $check = $conexion->prepare("SELECT id FROM usuarios WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        $mensaje = "Ya existe una cuenta con ese correo electrónico.";
        $tipo    = "danger";
    } else {
        $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $nombre, $email, $password, $rol);

        if ($stmt->execute()) {
            $mensaje = "Usuario registrado correctamente.";
            $tipo    = "success";
        } else {
            $mensaje = "Error al registrar el usuario.";
            $tipo    = "danger";
        }
        $stmt->close();
    }
    $check->close();
}
?>

<div class="mb-4">
    <h4 style="font-weight:800;color:#0f2544;margin:0;">Registrar Usuario</h4>
    <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">Crea una nueva cuenta de acceso al panel.</p>
</div>

<div class="row">
    <div class="col-md-7 col-lg-5">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Datos del nuevo usuario</h6>
            </div>
            <div style="padding:24px;">

                <?php if (!empty($mensaje)): ?>
                <div class="alert alert-<?= $tipo ?> d-flex align-items-center gap-2 mb-3" style="border-radius:9px;font-size:.86rem;">
                    <i class="bi <?= $tipo == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
                    <?= htmlspecialchars($mensaje) ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label style="font-size:.8rem;font-weight:600;color:#0f2544;display:block;margin-bottom:5px;">Nombre completo</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Ej. María López" required>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:.8rem;font-weight:600;color:#0f2544;display:block;margin-bottom:5px;">Correo electrónico</label>
                        <input type="email" name="email" class="form-control" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="mb-3">
                        <label style="font-size:.8rem;font-weight:600;color:#0f2544;display:block;margin-bottom:5px;">Contraseña</label>
                        <input type="password" name="password" class="form-control" placeholder="Mínimo 6 caracteres" required minlength="6">
                    </div>
                    <div class="mb-4">
                        <label style="font-size:.8rem;font-weight:600;color:#0f2544;display:block;margin-bottom:5px;">Rol</label>
                        <select name="rol" class="form-control">
                            <option value="usuario">Usuario</option>
                            <option value="admin">Administrador</option>
                        </select>
                    </div>
                    <button type="submit" class="btn w-100"
                        style="background:#00c8a0;color:#0d1117;font-weight:700;border-radius:50px;padding:11px;border:none;cursor:pointer;">
                        <i class="bi bi-person-plus me-1"></i>Registrar Usuario
                    </button>
                </form>

            </div>
        </div>
    </div>
</div>