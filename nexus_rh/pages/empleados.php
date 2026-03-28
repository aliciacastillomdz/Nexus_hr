<?php
require_once("../sistema/conexion.php");

$es_admin = ($_SESSION['rol'] ?? '') === 'admin';
$msg      = "";
$msg_tipo = "";
$vista    = $_GET['vista'] ?? 'lista';
$uid      = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ── Acciones POST (solo admin) ───────────────────────────────
if ($es_admin && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Crear integrante + cuenta
    if ($accion === 'crear') {
        $nombre        = $conexion->real_escape_string(trim($_POST['nombre']));
        $email         = $conexion->real_escape_string(trim($_POST['email']));
        $password      = trim($_POST['password']);
        $puesto        = $conexion->real_escape_string(trim($_POST['puesto']));
        $telefono      = $conexion->real_escape_string(trim($_POST['telefono']));
        $fecha_ingreso = $_POST['fecha_ingreso'] ?: null;
        $notas         = $conexion->real_escape_string(trim($_POST['notas']));
        $fecha_sql     = $fecha_ingreso ? "'$fecha_ingreso'" : "NULL";

        // Verificar email único
        $check = $conexion->query("SELECT id FROM usuarios WHERE email='$email'");
        if ($check && $check->num_rows > 0) {
            $msg = "Ya existe una cuenta con ese correo."; $msg_tipo = "danger"; $vista = 'nuevo';
        } else {
            $hash = $conexion->real_escape_string(password_hash($password, PASSWORD_DEFAULT));
            $conexion->query("INSERT INTO usuarios (nombre, email, password, rol, puesto, telefono, fecha_ingreso, notas)
                VALUES ('$nombre','$email','$hash','usuario','$puesto','$telefono',$fecha_sql,'$notas')");
            $msg = "Integrante agregado y cuenta creada correctamente."; $msg_tipo = "success"; $vista = 'lista';
        }
    }

    // Editar integrante
    if ($accion === 'editar') {
        $id            = (int)$_POST['uid'];
        $nombre        = $conexion->real_escape_string(trim($_POST['nombre']));
        $email         = $conexion->real_escape_string(trim($_POST['email']));
        $puesto        = $conexion->real_escape_string(trim($_POST['puesto']));
        $telefono      = $conexion->real_escape_string(trim($_POST['telefono']));
        $fecha_ingreso = $_POST['fecha_ingreso'] ?: null;
        $notas         = $conexion->real_escape_string(trim($_POST['notas']));
        $rol           = in_array($_POST['rol'], ['admin','usuario']) ? $_POST['rol'] : 'usuario';
        $fecha_sql     = $fecha_ingreso ? "'$fecha_ingreso'" : "NULL";

        $conexion->query("UPDATE usuarios SET nombre='$nombre', email='$email', puesto='$puesto',
            telefono='$telefono', fecha_ingreso=$fecha_sql, notas='$notas', rol='$rol' WHERE id=$id");

        // Cambiar contraseña solo si se puso una nueva
        if (!empty(trim($_POST['password'] ?? ''))) {
            $hash = $conexion->real_escape_string(password_hash(trim($_POST['password']), PASSWORD_DEFAULT));
            $conexion->query("UPDATE usuarios SET password='$hash' WHERE id=$id");
        }

        $msg = "Datos actualizados."; $msg_tipo = "success"; $vista = 'lista';
    }

    // Eliminar
    if ($accion === 'eliminar') {
        $id = (int)$_POST['uid'];
        // No permitir eliminar al admin actual
        if ($id !== (int)$_SESSION['id']) {
            $conexion->query("DELETE FROM usuarios WHERE id=$id");
            $msg = "Integrante eliminado."; $msg_tipo = "success";
        } else {
            $msg = "No puedes eliminar tu propia cuenta."; $msg_tipo = "danger";
        }
        $vista = 'lista';
    }
}

// ── Cargar usuario para editar ───────────────────────────────
$editando = null;
if ($vista === 'editar' && $uid > 0 && $es_admin) {
    $editando = $conexion->query("SELECT * FROM usuarios WHERE id=$uid")->fetch_assoc();
    if (!$editando) $vista = 'lista';
}

function colorAvatar($nombre) {
    $colores = ['#0f2544','#1a3a6e','#0f6b62','#1e8449','#922b21','#9a7d0a','#6c3483'];
    return $colores[ord($nombre[0] ?? 'A') % count($colores)];
}
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;">Equipo Nexus RH</h4>
        <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">Directorio del equipo y cuentas de acceso.</p>
    </div>
    <?php if ($es_admin && $vista === 'lista'): ?>
    <a href="index.php?page=empleados&vista=nuevo"
       style="background:#0f2544;color:#66FCF1;border-radius:50px;padding:9px 22px;font-weight:700;font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <i class="bi bi-person-plus"></i> Agregar integrante
    </a>
    <?php endif; ?>
</div>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msg_tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;font-size:.88rem;">
    <i class="bi <?= $msg_tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<?php
// ════════════════════════════════════════════════════════════
//  FORMULARIO NUEVO / EDITAR
// ════════════════════════════════════════════════════════════
if (($vista === 'nuevo' || $vista === 'editar') && $es_admin):
    $e = $editando;
?>
<div class="panel-card">
    <div class="panel-card-header" style="background:#0f2544;">
        <h6 style="color:#66FCF1;font-weight:700;margin:0;">
            <i class="bi bi-person-badge me-2"></i>
            <?= $e ? 'Editar — ' . htmlspecialchars($e['nombre']) : 'Nuevo integrante' ?>
        </h6>
    </div>
    <div style="padding:28px;">

        <!-- ► FORM PRINCIPAL: solo crear/editar -->
        <form method="POST">
            <input type="hidden" name="accion" value="<?= $e ? 'editar' : 'crear' ?>">
            <?php if ($e): ?><input type="hidden" name="uid" value="<?= $e['id'] ?>"><?php endif; ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control" required
                           value="<?= htmlspecialchars($e['nombre'] ?? '') ?>"
                           placeholder="Ej. María González"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-6">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Puesto</label>
                    <input type="text" name="puesto" class="form-control"
                           value="<?= htmlspecialchars($e['puesto'] ?? '') ?>"
                           placeholder="Ej. Reclutadora Senior"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-5">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Email *</label>
                    <input type="email" name="email" class="form-control" required
                           value="<?= htmlspecialchars($e['email'] ?? '') ?>"
                           placeholder="correo@nexusrh.com"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-4">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Teléfono</label>
                    <input type="text" name="telefono" class="form-control"
                           value="<?= htmlspecialchars($e['telefono'] ?? '') ?>"
                           placeholder="+52 81 ..."
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-3">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Fecha de ingreso</label>
                    <input type="date" name="fecha_ingreso" class="form-control"
                           value="<?= $e['fecha_ingreso'] ?? '' ?>"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-6">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">
                        <?= $e ? 'Nueva contraseña (vacío = no cambiar)' : 'Contraseña de acceso *' ?>
                    </label>
                    <input type="password" name="password" class="form-control"
                           <?= $e ? '' : 'required minlength="6"' ?>
                           placeholder="<?= $e ? 'Dejar vacío para no cambiar' : 'Mínimo 6 caracteres' ?>"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <?php if ($e): ?>
                <div class="col-md-3">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Rol</label>
                    <select name="rol" class="form-select" style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                        <option value="usuario" <?= ($e['rol'] === 'usuario') ? 'selected' : '' ?>>Usuario</option>
                        <option value="admin"   <?= ($e['rol'] === 'admin')   ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <?php endif; ?>
                <div class="col-12">
                    <label style="font-size:.8rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Notas</label>
                    <textarea name="notas" class="form-control" rows="2"
                              placeholder="Información adicional..."
                              style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;resize:vertical;"><?= htmlspecialchars($e['notas'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Botones guardar y cancelar -->
            <div style="display:flex;gap:10px;margin-top:20px;flex-wrap:wrap;">
                <button type="submit"
                        style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:11px 28px;font-weight:700;font-size:.9rem;cursor:pointer;">
                    <i class="bi bi-check2 me-1"></i><?= $e ? 'Guardar cambios' : 'Agregar integrante' ?>
                </button>
                <a href="index.php?page=empleados"
                   style="background:#f4f7fb;color:#0f2544;border:1.5px solid #e2e8f0;border-radius:9px;padding:11px 22px;font-weight:600;font-size:.9rem;text-decoration:none;">
                    Cancelar
                </a>
            </div>
        </form>
        <!-- ► FIN FORM PRINCIPAL -->

        <!-- ► FORM ELIMINAR: separado, fuera del form principal -->
        <?php if ($e && $e['id'] != $_SESSION['id']): ?>
        <form method="POST" style="margin-top:10px;"
              onsubmit="return confirm('¿Eliminar a <?= htmlspecialchars($e['nombre']) ?>? También se eliminará su acceso al sistema.')">
            <input type="hidden" name="accion" value="eliminar">
            <input type="hidden" name="uid"    value="<?= $e['id'] ?>">
            <button type="submit"
                    style="background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.2);border-radius:9px;padding:11px 20px;font-weight:600;font-size:.9rem;cursor:pointer;">
                <i class="bi bi-trash"></i> Eliminar
            </button>
        </form>
        <?php endif; ?>
        <!-- ► FIN FORM ELIMINAR -->

    </div>
</div>

<?php
// ════════════════════════════════════════════════════════════
//  LISTA DEL EQUIPO
// ════════════════════════════════════════════════════════════
else:
    $equipo  = $conexion->query("SELECT * FROM usuarios ORDER BY rol DESC, nombre ASC");
    $total   = $equipo ? $equipo->num_rows : 0;
    $admins  = (int)$conexion->query("SELECT COUNT(*) as c FROM usuarios WHERE rol='admin'")->fetch_assoc()['c'];
    $usuarios = $total - $admins;
?>

<!-- Stats -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div style="font-size:.72rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Total</div>
            <div style="font-size:2rem;font-weight:800;color:#0f2544;line-height:1;"><?= $total ?></div>
            <div style="font-size:.75rem;color:#8896a7;margin-top:4px;">integrantes</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card" style="background:rgba(102,252,241,.06);border:1.5px solid rgba(102,252,241,.2);">
            <div style="font-size:.72rem;font-weight:700;color:#0f6b62;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Admins</div>
            <div style="font-size:2rem;font-weight:800;color:#0f6b62;line-height:1;"><?= $admins ?></div>
            <div style="font-size:.75rem;color:#0f6b62;margin-top:4px;">administradores</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div style="font-size:.72rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Usuarios</div>
            <div style="font-size:2rem;font-weight:800;color:#0f2544;line-height:1;"><?= $usuarios ?></div>
            <div style="font-size:.75rem;color:#8896a7;margin-top:4px;">reclutadores</div>
        </div>
    </div>
</div>

<!-- Tarjetas -->
<?php if ($equipo && $equipo->num_rows > 0): ?>
<div class="row g-3">
    <?php while ($emp = $equipo->fetch_assoc()):
        $inicial = strtoupper(substr($emp['nombre'], 0, 1));
        $color   = colorAvatar($emp['nombre']);
        $es_yo   = $emp['id'] == $_SESSION['id'];
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="panel-card h-100">

            <!-- Header -->
            <div style="background:#0f2544;padding:18px 20px;display:flex;align-items:center;gap:12px;">
                <div style="width:46px;height:46px;background:<?= $color ?>;border:2px solid rgba(102,252,241,.3);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:1.15rem;color:#66FCF1;flex-shrink:0;">
                    <?= $inicial ?>
                </div>
                <div style="overflow:hidden;flex:1;">
                    <div style="font-weight:700;color:#fff;font-size:.92rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        <?= htmlspecialchars($emp['nombre']) ?>
                        <?php if ($es_yo): ?>
                        <span style="font-size:.68rem;background:rgba(102,252,241,.15);color:#66FCF1;padding:2px 7px;border-radius:50px;margin-left:4px;">Tú</span>
                        <?php endif; ?>
                    </div>
                    <div style="font-size:.76rem;color:#66FCF1;opacity:.8;margin-top:2px;">
                        <?= htmlspecialchars($emp['puesto'] ?? ($emp['rol'] === 'admin' ? 'Administrador' : 'Reclutador')) ?>
                    </div>
                </div>
                <!-- Badge rol -->
                <span style="font-size:.68rem;padding:3px 9px;border-radius:50px;font-weight:700;flex-shrink:0;
                      background:<?= $emp['rol'] === 'admin' ? 'rgba(102,252,241,.2)' : 'rgba(255,255,255,.1)' ?>;
                      color:<?= $emp['rol'] === 'admin' ? '#66FCF1' : 'rgba(255,255,255,.6)' ?>;">
                    <?= $emp['rol'] ?>
                </span>
            </div>

            <!-- Info -->
            <div style="padding:14px 18px;display:flex;flex-direction:column;gap:7px;">
                <div style="display:flex;align-items:center;gap:8px;font-size:.84rem;color:#4a5568;">
                    <i class="bi bi-envelope" style="color:#66FCF1;width:16px;flex-shrink:0;"></i>
                    <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($emp['email']) ?></span>
                </div>
                <?php if (!empty($emp['telefono'])): ?>
                <div style="display:flex;align-items:center;gap:8px;font-size:.84rem;color:#4a5568;">
                    <i class="bi bi-telephone" style="color:#66FCF1;width:16px;flex-shrink:0;"></i>
                    <?= htmlspecialchars($emp['telefono']) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($emp['fecha_ingreso'])): ?>
                <div style="display:flex;align-items:center;gap:8px;font-size:.84rem;color:#4a5568;">
                    <i class="bi bi-calendar3" style="color:#66FCF1;width:16px;flex-shrink:0;"></i>
                    Desde <?= date('d/m/Y', strtotime($emp['fecha_ingreso'])) ?>
                </div>
                <?php endif; ?>
                <?php if (!empty($emp['notas'])): ?>
                <div style="font-size:.78rem;color:#8896a7;background:#f4f7fb;border-radius:8px;padding:7px 10px;margin-top:2px;">
                    <?= htmlspecialchars($emp['notas']) ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Botón editar (solo admin) -->
            <?php if ($es_admin): ?>
            <div style="padding:0 14px 14px;">
                <a href="index.php?page=empleados&vista=editar&id=<?= $emp['id'] ?>"
                   style="display:flex;align-items:center;justify-content:center;gap:6px;background:#f4f7fb;color:#0f2544;border:1.5px solid #e2e8f0;border-radius:9px;padding:8px;font-weight:600;font-size:.83rem;text-decoration:none;transition:.2s;"
                   onmouseover="this.style.borderColor='#0f2544'" onmouseout="this.style.borderColor='#e2e8f0'">
                    <i class="bi bi-pencil"></i> Editar
                </a>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endwhile; ?>
</div>

<?php else: ?>
<div class="panel-card">
    <div style="padding:60px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-people" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">No hay integrantes registrados.</p>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>