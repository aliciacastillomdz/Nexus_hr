<?php
require_once("../sistema/conexion.php");

$usuario_id  = $_SESSION['id'];
$usuario_rol = $_SESSION['rol'];
$msg      = "";
$msg_tipo = "";

// ── Determinar vista ─────────────────────────────────────────
$vista = $_GET['vista'] ?? 'lista';
$sol_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Colores por estatus
function colorEstatus($e) {
    return match($e) {
        'Nuevo contacto'      => ['bg' => '#e8f4fd', 'color' => '#1a6fa8', 'dot' => '#3498db'],
        'Entrevista agendada' => ['bg' => '#fef9e7', 'color' => '#9a7d0a', 'dot' => '#f1c40f'],
        'Entrevista realizada'=> ['bg' => '#eafaf1', 'color' => '#1e8449', 'dot' => '#2ecc71'],
        'Contratado'          => ['bg' => 'rgba(102,252,241,.12)', 'color' => '#0f6b62', 'dot' => '#66FCF1'],
        'No continuó'         => ['bg' => '#fdf2f2', 'color' => '#922b21', 'dot' => '#e74c3c'],
        default               => ['bg' => '#f4f7fb', 'color' => '#8896a7', 'dot' => '#8896a7'],
    };
}

// ── ACCIONES POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Crear solicitante
    if ($accion === 'crear') {
        $uid  = (int)$usuario_id;
        $eid  = (int)$_POST['empresa_id'] ?: 'NULL';
        $vid  = (int)$_POST['vacante_id'] ?: 'NULL';
        $nom  = $conexion->real_escape_string(trim($_POST['nombre']));
        $edad = (int)$_POST['edad'] ?: 'NULL';
        $col  = $conexion->real_escape_string(trim($_POST['colonia']));
        $tel  = $conexion->real_escape_string(trim($_POST['telefono']));
        $est  = $conexion->real_escape_string($_POST['estudios']);
        $exp  = $conexion->real_escape_string(trim($_POST['experiencia']));
        $cita = $_POST['fecha_cita'] ? "'" . $conexion->real_escape_string($_POST['fecha_cita']) . "'" : 'NULL';
        $inf  = isset($_POST['infonavit_fonacot']) ? 1 : 0;
        $des  = $conexion->real_escape_string(trim($_POST['descuento_monto']));
        $ade  = isset($_POST['adeudo_santander']) ? 1 : 0;
        $obs  = $conexion->real_escape_string(trim($_POST['observaciones']));
        $sta  = $conexion->real_escape_string($_POST['estatus']);

        $sql = "INSERT INTO solicitantes
            (usuario_id, empresa_id, vacante_id, nombre, edad, colonia, telefono,
             estudios, experiencia, fecha_cita, infonavit_fonacot, descuento_monto,
             adeudo_santander, observaciones, estatus)
            VALUES ($uid, $eid, $vid, '$nom', $edad, '$col', '$tel',
                    '$est', '$exp', $cita, $inf, '$des', $ade, '$obs', '$sta')";

        if ($conexion->query($sql)) {
            $msg = "Solicitante registrado correctamente."; $msg_tipo = "success";
            $vista = 'lista';
        } else {
            $msg = "Error al guardar: " . $conexion->error; $msg_tipo = "danger";
            $vista = 'nuevo';
        }
    }

    // Actualizar estatus rápido
    if ($accion === 'estatus') {
        $id      = (int)$_POST['sol_id'];
        $estatus = $_POST['estatus'];
        $where   = $usuario_rol === 'admin' ? "id = $id" : "id = $id AND usuario_id = $usuario_id";
        $stmt = $conexion->prepare("UPDATE solicitantes SET estatus=? WHERE $where");
        $stmt->bind_param("s", $estatus);
        $stmt->execute(); $stmt->close();
        $msg = "Estatus actualizado."; $msg_tipo = "success";
    }

    // Editar solicitante
    if ($accion === 'editar') {
        $id    = (int)$_POST['sol_id'];
        $where = $usuario_rol === 'admin' ? "id = $id" : "id = $id AND usuario_id = $usuario_id";
        $eid  = (int)$_POST['empresa_id'] ?: 'NULL';
        $vid  = (int)$_POST['vacante_id'] ?: 'NULL';
        $nom  = $conexion->real_escape_string(trim($_POST['nombre']));
        $edad = (int)$_POST['edad'] ?: 'NULL';
        $col  = $conexion->real_escape_string(trim($_POST['colonia']));
        $tel  = $conexion->real_escape_string(trim($_POST['telefono']));
        $est  = $conexion->real_escape_string($_POST['estudios']);
        $exp  = $conexion->real_escape_string(trim($_POST['experiencia']));
        $cita = $_POST['fecha_cita'] ? "'" . $conexion->real_escape_string($_POST['fecha_cita']) . "'" : 'NULL';
        $inf  = isset($_POST['infonavit_fonacot']) ? 1 : 0;
        $des  = $conexion->real_escape_string(trim($_POST['descuento_monto']));
        $ade  = isset($_POST['adeudo_santander']) ? 1 : 0;
        $obs  = $conexion->real_escape_string(trim($_POST['observaciones']));
        $sta  = $conexion->real_escape_string($_POST['estatus']);

        $sql = "UPDATE solicitantes SET
            empresa_id=$eid, vacante_id=$vid, nombre='$nom', edad=$edad,
            colonia='$col', telefono='$tel', estudios='$est', experiencia='$exp',
            fecha_cita=$cita, infonavit_fonacot=$inf, descuento_monto='$des',
            adeudo_santander=$ade, observaciones='$obs', estatus='$sta'
            WHERE $where";

        $conexion->query($sql);
        $msg = "Solicitante actualizado."; $msg_tipo = "success";
        $vista = 'lista';
    }

    // Eliminar
    if ($accion === 'eliminar') {
        $id    = (int)$_POST['sol_id'];
        $where = $usuario_rol === 'admin' ? "id = $id" : "id = $id AND usuario_id = $usuario_id";
        $conexion->query("DELETE FROM solicitantes WHERE $where");
        $msg = "Solicitante eliminado."; $msg_tipo = "success";
        $vista = 'lista';
    }
}

// Cargar datos auxiliares
$empresas = $conexion->query("SELECT id, nombre FROM empresas WHERE activo=1 ORDER BY nombre ASC");
$estatus_list = ['Nuevo contacto','Entrevista agendada','Entrevista realizada','Contratado','No continuó'];
$estudios_list = ['Primaria','Secundaria','Preparatoria','Técnico','Licenciatura','Posgrado','Otro'];

// ════════════════════════════════════════════════════════════
//  VISTA: NUEVO SOLICITANTE
// ════════════════════════════════════════════════════════════
if ($vista === 'nuevo' || $vista === 'editar'):
    $s = null;
    if ($vista === 'editar' && $sol_id > 0) {
        $where = $usuario_rol === 'admin' ? "id = $sol_id" : "id = $sol_id AND usuario_id = $usuario_id";
        $s = $conexion->query("SELECT * FROM solicitantes WHERE $where")->fetch_assoc();
        if (!$s) { header("Location: index.php?page=solicitantes"); exit(); }
    }
    $accion_form = $s ? 'editar' : 'crear';
    $titulo_form  = $s ? 'Editar Solicitante' : 'Nuevo Solicitante';
?>

<div class="d-flex align-items-center gap-3 mb-4">
    <a href="index.php?page=solicitantes"
       style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:#f4f7fb;border-radius:9px;border:1.5px solid #e2e8f0;text-decoration:none;color:#0f2544;flex-shrink:0;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;"><?= $titulo_form ?></h4>
        <p style="color:#8896a7;margin:2px 0 0;font-size:.86rem;">Completa los datos del solicitante.</p>
    </div>
</div>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msg_tipo ?> mb-4" style="border-radius:10px;font-size:.88rem;"><?= htmlspecialchars($msg) ?></div>
<?php endif; ?>

<div class="panel-card">
    <div style="padding:28px;">
        <form method="POST">
            <input type="hidden" name="accion"  value="<?= $accion_form ?>">
            <?php if ($s): ?><input type="hidden" name="sol_id" value="<?= $s['id'] ?>"><?php endif; ?>

            <!-- Empresa y Puesto -->
            <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:12px;">Empresa y Puesto</p>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Empresa</label>
                    <select name="empresa_id" class="form-select" style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;" onchange="cargarVacantes(this.value)">
                        <option value="">— Selecciona empresa —</option>
                        <?php
                        $empresas->data_seek(0);
                        while ($e = $empresas->fetch_assoc()): ?>
                        <option value="<?= $e['id'] ?>" <?= ($s && $s['empresa_id'] == $e['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($e['nombre']) ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Puesto / Vacante</label>
                    <select name="vacante_id" id="select-vacante" class="form-select" style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                        <option value="">— Selecciona empresa primero —</option>
                        <?php if ($s && $s['vacante_id']): ?>
                        <option value="<?= $s['vacante_id'] ?>" selected>
                            <?= htmlspecialchars($conexion->query("SELECT nombre FROM vacantes WHERE id={$s['vacante_id']}")->fetch_assoc()['nombre'] ?? '') ?>
                        </option>
                        <?php endif; ?>
                    </select>
                </div>
            </div>

            <!-- Datos personales -->
            <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:12px;">Datos Personales</p>
            <div class="row g-3 mb-4">
                <div class="col-md-5">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Nombre completo *</label>
                    <input type="text" name="nombre" class="form-control" required value="<?= htmlspecialchars($s['nombre'] ?? '') ?>"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-2">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Edad</label>
                    <input type="number" name="edad" class="form-control" min="16" max="99" value="<?= htmlspecialchars($s['edad'] ?? '') ?>"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-5">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Colonia</label>
                    <input type="text" name="colonia" class="form-control" value="<?= htmlspecialchars($s['colonia'] ?? '') ?>"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Teléfono / WhatsApp</label>
                    <input type="text" name="telefono" class="form-control" value="<?= htmlspecialchars($s['telefono'] ?? '') ?>"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Último grado de estudios</label>
                    <select name="estudios" class="form-select" style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                        <option value="">— Selecciona —</option>
                        <?php foreach ($estudios_list as $est): ?>
                        <option value="<?= $est ?>" <?= ($s && $s['estudios'] === $est) ? 'selected' : '' ?>><?= $est ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Fecha de cita</label>
                    <input type="date" name="fecha_cita" class="form-control" value="<?= $s['fecha_cita'] ?? '' ?>"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-12">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Experiencia</label>
                    <textarea name="experiencia" class="form-control" rows="2"
                              style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;resize:vertical;"><?= htmlspecialchars($s['experiencia'] ?? '') ?></textarea>
                </div>
            </div>

            <!-- Descuentos -->
            <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:12px;">Descuentos y Adeudos</p>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div style="background:#f4f7fb;border-radius:9px;padding:14px 16px;border:1.5px solid #e2e8f0;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;margin-bottom:10px;">
                            <input type="checkbox" name="infonavit_fonacot" <?= ($s && $s['infonavit_fonacot']) ? 'checked' : '' ?>
                                   onchange="document.getElementById('monto-wrap').style.display=this.checked?'block':'none'">
                            <span style="font-size:.88rem;font-weight:600;color:#0f2544;">¿Tiene descuentos de Infonavit o Fonacot?</span>
                        </label>
                        <div id="monto-wrap" style="display:<?= ($s && $s['infonavit_fonacot']) ? 'block' : 'none' ?>;">
                            <input type="text" name="descuento_monto" class="form-control"
                                   placeholder="Ej. $800 Infonavit + $400 Fonacot"
                                   value="<?= htmlspecialchars($s['descuento_monto'] ?? '') ?>"
                                   style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div style="background:#f4f7fb;border-radius:9px;padding:14px 16px;border:1.5px solid #e2e8f0;height:100%;display:flex;align-items:center;">
                        <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                            <input type="checkbox" name="adeudo_santander" <?= ($s && $s['adeudo_santander']) ? 'checked' : '' ?>>
                            <span style="font-size:.88rem;font-weight:600;color:#0f2544;">¿Tiene adeudo con Santander?</span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Estatus y observaciones -->
            <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:12px;">Seguimiento</p>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Estatus</label>
                    <select name="estatus" class="form-select" style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                        <?php foreach ($estatus_list as $est): ?>
                        <option value="<?= $est ?>" <?= ($s && $s['estatus'] === $est) ? 'selected' : '' ?>><?= $est ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-8">
                    <label class="form-label" style="font-size:.82rem;font-weight:600;color:#0f2544;">Observaciones</label>
                    <textarea name="observaciones" class="form-control" rows="2"
                              style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;resize:vertical;"><?= htmlspecialchars($s['observaciones'] ?? '') ?></textarea>
                </div>
            </div>

            <div style="display:flex;gap:10px;">
                <button type="submit"
                        style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:11px 28px;font-weight:700;font-size:.9rem;cursor:pointer;">
                    <i class="bi bi-check2 me-1"></i><?= $s ? 'Guardar cambios' : 'Registrar solicitante' ?>
                </button>
                <a href="index.php?page=solicitantes"
                   style="background:#f4f7fb;color:#0f2544;border:1.5px solid #e2e8f0;border-radius:9px;padding:11px 22px;font-weight:600;font-size:.9rem;text-decoration:none;">
                    Cancelar
                </a>
                <?php if ($s): ?>
                <form method="POST" style="margin:0;" onsubmit="return confirm('¿Eliminar este solicitante?')">
                    <input type="hidden" name="accion"  value="eliminar">
                    <input type="hidden" name="sol_id"  value="<?= $s['id'] ?>">
                    <button type="submit"
                            style="background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.2);border-radius:9px;padding:11px 20px;font-weight:600;font-size:.9rem;cursor:pointer;">
                        <i class="bi bi-trash"></i> Eliminar
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<script>
function cargarVacantes(empresa_id) {
    const sel = document.getElementById('select-vacante');
    if (!empresa_id) {
        sel.innerHTML = '<option value="">— Selecciona empresa primero —</option>';
        return;
    }
    fetch('../pages/ajax_vacantes.php?empresa=' + empresa_id)
        .then(r => r.json())
        .then(data => {
            sel.innerHTML = '<option value="">— Selecciona vacante —</option>';
            data.forEach(v => {
                sel.innerHTML += `<option value="${v.id}">${v.nombre}</option>`;
            });
        });
}
</script>

<!-- Script: actualizar URL del botón exportar cuando se aplican filtros -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.querySelector('form[method="GET"]');
    const btnExportar = document.getElementById('btn-exportar');
    if (!form || !btnExportar) return;

    form.addEventListener('submit', function () {
        const base = '../pages/exportar_solicitantes.php';
        const params = new URLSearchParams(new FormData(form));
        params.delete('page');
        const query = params.toString();
        btnExportar.href = base + (query ? '?' + query : '');
    });
});
</script>

<?php
// ════════════════════════════════════════════════════════════
//  VISTA: LISTA
// ════════════════════════════════════════════════════════════
else:

// Filtros
$filtro_estatus  = $_GET['estatus']  ?? '';
$filtro_empresa  = isset($_GET['empresa'])  ? (int)$_GET['empresa']  : 0;
$filtro_buscar   = trim($_GET['buscar'] ?? '');

$where_parts = [];
if ($usuario_rol !== 'admin') $where_parts[] = "s.usuario_id = $usuario_id";
if ($filtro_estatus) $where_parts[] = "s.estatus = '" . $conexion->real_escape_string($filtro_estatus) . "'";
if ($filtro_empresa) $where_parts[] = "s.empresa_id = $filtro_empresa";
if ($filtro_buscar)  $where_parts[] = "(s.nombre LIKE '%" . $conexion->real_escape_string($filtro_buscar) . "%' OR s.telefono LIKE '%" . $conexion->real_escape_string($filtro_buscar) . "%')";
$where_sql = $where_parts ? "WHERE " . implode(" AND ", $where_parts) : "";

$solicitantes = $conexion->query("
    SELECT s.*, e.nombre as empresa_nombre, v.nombre as vacante_nombre, u.nombre as reclutador
    FROM solicitantes s
    LEFT JOIN empresas e ON e.id = s.empresa_id
    LEFT JOIN vacantes v ON v.id = s.vacante_id
    LEFT JOIN usuarios u ON u.id = s.usuario_id
    $where_sql
    ORDER BY s.fecha_cita ASC, s.created_at DESC
");

// Contadores por estatus (para el usuario actual o todos si admin)
$where_count = $usuario_rol !== 'admin' ? "WHERE usuario_id = $usuario_id" : "";
$contadores = [];
$cnt = $conexion->query("SELECT estatus, COUNT(*) as c FROM solicitantes $where_count GROUP BY estatus");
while ($c = $cnt->fetch_assoc()) $contadores[$c['estatus']] = $c['c'];
$total = array_sum($contadores);
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;">Solicitantes</h4>
        <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">
            <?= $usuario_rol === 'admin' ? 'Todos los candidatos registrados.' : 'Candidatos que has registrado.' ?>
        </p>
    </div>
    <div style="display:flex;gap:10px;align-items:center;">
        <!-- Botón exportar Excel -->
        <a id="btn-exportar"
           href="<?php
                $params = [];
                if (!empty($filtro_estatus)) $params[] = 'estatus=' . urlencode($filtro_estatus);
                if ($filtro_empresa)         $params[] = 'empresa=' . $filtro_empresa;
                if (!empty($filtro_buscar))  $params[] = 'buscar=' . urlencode($filtro_buscar);
                echo '../pages/exportar_solicitantes.php' . (count($params) ? '?' . implode('&', $params) : '');
           ?>"
           style="background:#f4f7fb;color:#0f2544;border:1.5px solid #e2e8f0;border-radius:50px;padding:9px 20px;font-weight:600;font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:.2s;"
           onmouseover="this.style.borderColor='#0f2544'" onmouseout="this.style.borderColor='#e2e8f0'">
            <i class="bi bi-file-earmark-excel" style="color:#1e7e34;"></i> Exportar Excel
        </a>
        <a href="index.php?page=solicitantes&vista=nuevo"
           style="background:#0f2544;color:#66FCF1;border-radius:50px;padding:9px 22px;font-weight:700;font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
            <i class="bi bi-plus-lg"></i> Nuevo solicitante
        </a>
    </div>
</div>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msg_tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;font-size:.88rem;">
    <i class="bi <?= $msg_tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- Resumen por estatus -->
<div class="row g-3 mb-4">
    <?php
    $todos_estatus = ['Nuevo contacto','Entrevista agendada','Entrevista realizada','Contratado','No continuó'];
    foreach ($todos_estatus as $est):
        $c  = $contadores[$est] ?? 0;
        $cs = colorEstatus($est);
    ?>
    <div class="col">
        <a href="index.php?page=solicitantes&estatus=<?= urlencode($est) ?>" style="text-decoration:none;">
            <div style="background:<?= $cs['bg'] ?>;border-radius:10px;padding:14px 16px;border:1.5px solid <?= $filtro_estatus === $est ? $cs['dot'] : 'transparent' ?>;transition:.2s;">
                <div style="font-size:1.6rem;font-weight:800;color:<?= $cs['color'] ?>;line-height:1;"><?= $c ?></div>
                <div style="font-size:.75rem;font-weight:600;color:<?= $cs['color'] ?>;margin-top:4px;line-height:1.3;"><?= $est ?></div>
            </div>
        </a>
    </div>
    <?php endforeach; ?>
    <div class="col">
        <a href="index.php?page=solicitantes" style="text-decoration:none;">
            <div style="background:#f4f7fb;border-radius:10px;padding:14px 16px;border:1.5px solid <?= !$filtro_estatus ? '#0f2544' : 'transparent' ?>;transition:.2s;">
                <div style="font-size:1.6rem;font-weight:800;color:#0f2544;line-height:1;"><?= $total ?></div>
                <div style="font-size:.75rem;font-weight:600;color:#8896a7;margin-top:4px;">Total</div>
            </div>
        </a>
    </div>
</div>

<!-- Filtros -->
<form method="GET" id="form-filtros" style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px;">
    <input type="hidden" name="page" value="solicitantes">
    <input type="text" name="buscar" id="input-buscar" class="form-control"
           placeholder="Buscar por nombre o teléfono..."
           value="<?= htmlspecialchars($filtro_buscar) ?>"
           style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;max-width:260px;">
    <select name="empresa" class="form-select" onchange="document.getElementById('form-filtros').submit()"
            style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;max-width:200px;">
        <option value="">Todas las empresas</option>
        <?php $empresas->data_seek(0); while ($e = $empresas->fetch_assoc()): ?>
        <option value="<?= $e['id'] ?>" <?= $filtro_empresa === (int)$e['id'] ? 'selected' : '' ?>><?= htmlspecialchars($e['nombre']) ?></option>
        <?php endwhile; ?>
    </select>
    <select name="estatus" class="form-select" onchange="document.getElementById('form-filtros').submit()"
            style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;max-width:200px;">
        <option value="">Todos los estatus</option>
        <?php foreach ($estatus_list as $est): ?>
        <option value="<?= $est ?>" <?= $filtro_estatus === $est ? 'selected' : '' ?>><?= $est ?></option>
        <?php endforeach; ?>
    </select>
    <?php if ($filtro_buscar || $filtro_empresa || $filtro_estatus): ?>
    <a href="index.php?page=solicitantes"
       style="background:#f4f7fb;color:#0f2544;border:1.5px solid #e2e8f0;border-radius:9px;padding:9px 16px;font-size:.86rem;text-decoration:none;display:inline-flex;align-items:center;gap:4px;">
        <i class="bi bi-x"></i> Limpiar
    </a>
    <?php endif; ?>
</form>

<script>
// Buscador con debounce — espera 500ms tras dejar de escribir antes de enviar
(function () {
    const input  = document.getElementById('input-buscar');
    const form   = document.getElementById('form-filtros');
    let timer;
    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () {
            form.submit();
        }, 500);
    });
})();
</script>

<!-- Tabla -->
<div class="panel-card">
    <?php if ($solicitantes && $solicitantes->num_rows > 0): ?>
    <div style="overflow-x:auto;">
        <table class="table table-hover mb-0" style="font-size:.86rem;">
            <thead style="background:#f4f7fb;">
                <tr>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Nombre</th>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Empresa / Puesto</th>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Fecha cita</th>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Teléfono</th>
                    <?php if ($usuario_rol === 'admin'): ?>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Reclutador</th>
                    <?php endif; ?>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Estatus</th>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;"></th>
                </tr>
            </thead>
            <tbody>
            <?php while ($s = $solicitantes->fetch_assoc()):
                $cs = colorEstatus($s['estatus']);
                $cita = $s['fecha_cita'] ? date('d/m/Y', strtotime($s['fecha_cita'])) : '—';
                $hoy  = date('Y-m-d');
                $es_hoy = $s['fecha_cita'] === $hoy;
            ?>
            <tr style="<?= $es_hoy ? 'background:#fffbeb;' : '' ?>">
                <td style="padding:12px 16px;border-color:#f4f7fb;">
                    <div style="font-weight:700;color:#0f2544;"><?= htmlspecialchars($s['nombre']) ?></div>
                    <div style="font-size:.76rem;color:#8896a7;"><?= $s['edad'] ? $s['edad'] . ' años' : '' ?><?= $s['colonia'] ? ' · ' . htmlspecialchars($s['colonia']) : '' ?></div>
                    <?php if ($s['infonavit_fonacot'] || $s['adeudo_santander']): ?>
                    <div style="margin-top:3px;display:flex;gap:4px;flex-wrap:wrap;">
                        <?php if ($s['infonavit_fonacot']): ?>
                        <span style="font-size:.66rem;padding:2px 7px;border-radius:50px;background:#fef9e7;color:#9a7d0a;font-weight:600;">Infonavit/Fonacot</span>
                        <?php endif; ?>
                        <?php if ($s['adeudo_santander']): ?>
                        <span style="font-size:.66rem;padding:2px 7px;border-radius:50px;background:#fdf2f2;color:#922b21;font-weight:600;">Adeudo Santander</span>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;">
                    <div style="font-size:.86rem;color:#0f2544;font-weight:600;"><?= htmlspecialchars($s['empresa_nombre'] ?? '—') ?></div>
                    <div style="font-size:.76rem;color:#8896a7;"><?= htmlspecialchars($s['vacante_nombre'] ?? '') ?></div>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;white-space:nowrap;">
                    <?php if ($es_hoy): ?>
                    <span style="display:inline-flex;align-items:center;gap:4px;font-size:.78rem;font-weight:700;color:#9a7d0a;">
                        <i class="bi bi-calendar-check-fill"></i> Hoy
                    </span>
                    <?php else: ?>
                    <span style="font-size:.84rem;color:#4a5568;"><?= $cita ?></span>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;color:#4a5568;">
                    <?= htmlspecialchars($s['telefono'] ?? '—') ?>
                </td>
                <?php if ($usuario_rol === 'admin'): ?>
                <td style="padding:12px 16px;border-color:#f4f7fb;color:#8896a7;font-size:.83rem;">
                    <?= htmlspecialchars($s['reclutador']) ?>
                </td>
                <?php endif; ?>
                <td style="padding:12px 16px;border-color:#f4f7fb;">
                    <form method="POST" style="margin:0;">
                        <input type="hidden" name="accion"  value="estatus">
                        <input type="hidden" name="sol_id"  value="<?= $s['id'] ?>">
                        <select name="estatus" onchange="this.form.submit()"
                                style="font-size:.75rem;font-weight:700;padding:4px 8px;border-radius:50px;border:none;background:<?= $cs['bg'] ?>;color:<?= $cs['color'] ?>;cursor:pointer;">
                            <?php foreach ($estatus_list as $est): ?>
                            <option value="<?= $est ?>" <?= $s['estatus'] === $est ? 'selected' : '' ?>><?= $est ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;">
                    <a href="index.php?page=solicitantes&vista=editar&id=<?= $s['id'] ?>"
                       style="display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;background:#f4f7fb;border-radius:8px;border:1.5px solid #e2e8f0;text-decoration:none;color:#0f2544;font-size:.85rem;">
                        <i class="bi bi-pencil"></i>
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:60px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-people" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">No hay solicitantes<?= $filtro_buscar || $filtro_estatus || $filtro_empresa ? ' con esos filtros' : ' registrados aún' ?>.</p>
        <a href="index.php?page=solicitantes&vista=nuevo"
           style="color:#0f2544;font-size:.88rem;font-weight:600;display:inline-block;margin-top:8px;">+ Registrar solicitante</a>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>