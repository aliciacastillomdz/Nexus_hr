<?php
require_once("../sistema/conexion.php");

$usuario_id  = $_SESSION['id'];
$usuario_rol = $_SESSION['rol'];
$msg      = "";
$msg_tipo = "";

// ── Determinar vista ─────────────────────────────────────────
// ?view=grupos&empresa=ID  → vista de grupos de una empresa
// (sin parámetros)         → vista principal del kit
$vista      = $_GET['view']    ?? 'kit';
$empresa_id = isset($_GET['empresa']) ? (int)$_GET['empresa'] : 0;

// ── Guardar publicación ──────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'publicar') {
    $eid      = (int)$_POST['empresa_id'];
    $lid      = (int)$_POST['link_id'];
    $pub_link = trim($_POST['link_publicacion']);

    if (!empty($pub_link) && $eid > 0 && $lid > 0) {
        $stmt = $conexion->prepare("INSERT INTO publicaciones (usuario_id, empresa_id, link_id, link) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiis", $usuario_id, $eid, $lid, $pub_link);
        $stmt->execute() ? ($msg = "¡Publicación registrada!") : ($msg = "Error al guardar.");
        $msg_tipo = $stmt->affected_rows > 0 ? "success" : "danger";
        $stmt->close();
        // Mantener vista de grupos tras guardar
        $vista      = 'grupos';
        $empresa_id = $eid;
    }
}

// ════════════════════════════════════════════════════════════
//  VISTA: GRUPOS DE UNA EMPRESA
// ════════════════════════════════════════════════════════════
if ($vista === 'grupos' && $empresa_id > 0) {

    $emp = $conexion->query("SELECT * FROM empresas WHERE id = $empresa_id AND activo = 1")->fetch_assoc();
    if (!$emp) { header("Location: index.php?page=links"); exit(); }

    $links_empresa = $conexion->query("SELECT * FROM empresa_links WHERE empresa_id = $empresa_id ORDER BY id ASC");
?>

<!-- Encabezado vista grupos -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="index.php?page=links"
       style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:#f4f7fb;border-radius:9px;border:1.5px solid #e2e8f0;text-decoration:none;color:#0f2544;flex-shrink:0;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;"><?= htmlspecialchars($emp['nombre']) ?></h4>
        <p style="color:#8896a7;margin:2px 0 0;font-size:.86rem;">
            Selecciona el grupo, publica la vacante y registra tu link aquí.
        </p>
    </div>
</div>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msg_tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;font-size:.88rem;">
    <i class="bi <?= $msg_tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- Grupos / Links de Facebook -->
<?php if ($links_empresa && $links_empresa->num_rows > 0):
    while ($lnk = $links_empresa->fetch_assoc()):
        $lid = (int)$lnk['id'];
        $count_res = $conexion->query("SELECT COUNT(*) as c FROM publicaciones WHERE usuario_id = $usuario_id AND link_id = $lid");
        $mis_pubs  = (int)$count_res->fetch_assoc()['c'];
?>

<div style="border:1.5px solid #e2e8f0;border-radius:12px;padding:18px 20px;margin-bottom:14px;transition:border-color .2s;"
     onmouseover="this.style.borderColor='#66FCF1'" onmouseout="this.style.borderColor='#e2e8f0'">

    <!-- Link del grupo -->
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:14px;">
        <a href="<?= htmlspecialchars($lnk['url']) ?>" target="_blank"
           style="display:flex;align-items:center;gap:10px;flex:1;text-decoration:none;color:#0f2544;">
            <div style="width:40px;height:40px;background:#e7f3ff;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="bi bi-facebook" style="color:#1877f2;font-size:1.2rem;"></i>
            </div>
            <div style="overflow:hidden;">
                <div style="font-weight:700;font-size:.92rem;"><?= htmlspecialchars($lnk['titulo']) ?></div>
                <div style="font-size:.76rem;color:#8896a7;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <?= htmlspecialchars($lnk['url']) ?>
                </div>
            </div>
            <i class="bi bi-box-arrow-up-right ms-auto" style="color:#8896a7;font-size:.85rem;flex-shrink:0;"></i>
        </a>

        <!-- Contador publicaciones en este grupo -->
        <div style="flex-shrink:0;text-align:center;background:<?= $mis_pubs > 0 ? 'rgba(102,252,241,.1)' : '#f4f7fb' ?>;border-radius:10px;padding:7px 14px;border:1px solid <?= $mis_pubs > 0 ? 'rgba(102,252,241,.35)' : '#e2e8f0' ?>;">
            <div style="font-size:1.2rem;font-weight:800;color:#0f2544;line-height:1;"><?= $mis_pubs ?></div>
            <div style="font-size:.65rem;color:#8896a7;font-weight:600;text-transform:uppercase;letter-spacing:.8px;margin-top:2px;">publicadas</div>
        </div>
    </div>

    <!-- Input registrar publicación -->
    <form method="POST">
        <input type="hidden" name="accion"     value="publicar">
        <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
        <input type="hidden" name="link_id"    value="<?= $lid ?>">
        <div style="display:flex;gap:8px;">
            <input type="url" name="link_publicacion"
                   class="form-control"
                   placeholder="Pega aquí el link de tu publicación en este grupo..."
                   required
                   style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
            <button type="submit"
                    style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:9px 20px;font-weight:700;font-size:.85rem;cursor:pointer;white-space:nowrap;flex-shrink:0;transition:background .2s;"
                    onmouseover="this.style.background='#1a3a6e'" onmouseout="this.style.background='#0f2544'">
                <i class="bi bi-send me-1"></i>Registrar
            </button>
        </div>
    </form>

</div>

<?php endwhile;
else: ?>
<div class="panel-card">
    <div style="padding:50px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-link-45deg" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">No hay grupos configurados para esta empresa.</p>
    </div>
</div>
<?php endif; ?>

<!-- Mis publicaciones en esta empresa -->
<?php
$mis_pub_empresa = $conexion->query("
    SELECT p.link, p.fecha, el.titulo as grupo
    FROM publicaciones p
    LEFT JOIN empresa_links el ON el.id = p.link_id
    WHERE p.usuario_id = $usuario_id AND p.empresa_id = $empresa_id
    ORDER BY p.fecha DESC LIMIT 20
");
if ($mis_pub_empresa && $mis_pub_empresa->num_rows > 0): ?>
<div style="margin-top:28px;">
    <h6 style="font-weight:700;color:#0f2544;margin-bottom:12px;">Mis publicaciones en <?= htmlspecialchars($emp['nombre']) ?></h6>
    <div class="panel-card">
        <div style="overflow-x:auto;">
            <table class="table table-hover mb-0" style="font-size:.85rem;">
                <thead style="background:#f4f7fb;">
                    <tr>
                        <th style="padding:10px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Grupo</th>
                        <th style="padding:10px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Link publicado</th>
                        <th style="padding:10px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($p = $mis_pub_empresa->fetch_assoc()): ?>
                    <tr>
                        <td style="padding:10px 16px;border-color:#f4f7fb;color:#8896a7;font-size:.83rem;white-space:nowrap;">
                            <?= htmlspecialchars($p['grupo'] ?? '—') ?>
                        </td>
                        <td style="padding:10px 16px;border-color:#f4f7fb;max-width:260px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                            <a href="<?= htmlspecialchars($p['link']) ?>" target="_blank"
                               style="color:#0f2544;text-decoration:none;display:flex;align-items:center;gap:6px;">
                                <i class="bi bi-box-arrow-up-right" style="color:#66FCF1;flex-shrink:0;"></i>
                                <?= htmlspecialchars($p['link']) ?>
                            </a>
                        </td>
                        <td style="padding:10px 16px;border-color:#f4f7fb;color:#8896a7;white-space:nowrap;">
                            <?= date('d/m/Y H:i', strtotime($p['fecha'])) ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
// ════════════════════════════════════════════════════════════
//  VISTA: KIT PRINCIPAL
// ════════════════════════════════════════════════════════════
} else {
    $empresas = $conexion->query("SELECT * FROM empresas WHERE activo = 1 ORDER BY nombre ASC");
?>

<!-- Encabezado kit principal -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;">Kit de Documentos</h4>
        <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">
            Selecciona una empresa para acceder a sus recursos.
        </p>
    </div>
    <?php if ($usuario_rol === 'admin'): ?>
    <a href="index.php?page=gestionar_empresas"
       style="background:#0f2544;color:#66FCF1;border-radius:50px;padding:8px 20px;font-weight:700;font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;">
        <i class="bi bi-gear"></i> Gestionar
    </a>
    <?php endif; ?>
</div>

<!-- Tarjetas de empresas -->
<?php if ($empresas && $empresas->num_rows > 0): ?>
<div class="row g-3">
    <?php while ($empresa = $empresas->fetch_assoc()):
        $eid       = (int)$empresa['id'];
        $total_pub = (int)$conexion->query("SELECT COUNT(*) as c FROM publicaciones WHERE usuario_id = $usuario_id AND empresa_id = $eid")->fetch_assoc()['c'];
        $n_grupos  = (int)$conexion->query("SELECT COUNT(*) as c FROM empresa_links WHERE empresa_id = $eid")->fetch_assoc()['c'];
    ?>
    <div class="col-md-6 col-lg-4">
        <div class="panel-card h-100">

            <!-- Header -->
            <div style="background:#0f2544;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                <h6 style="color:#66FCF1;font-weight:700;margin:0;font-size:.95rem;">
                    <i class="bi bi-building me-2"></i><?= htmlspecialchars($empresa['nombre']) ?>
                </h6>
                <?php if ($total_pub > 0): ?>
                <span style="background:rgba(102,252,241,.15);color:#66FCF1;font-size:.72rem;font-weight:700;padding:3px 10px;border-radius:50px;">
                    <?= $total_pub ?> pub.
                </span>
                <?php endif; ?>
            </div>

            <!-- Cuerpo -->
            <div style="padding:18px 20px;display:flex;flex-direction:column;gap:10px;">

                <div style="font-size:.8rem;color:#8896a7;display:flex;align-items:center;gap:5px;">
                    <i class="bi bi-facebook" style="color:#1877f2;"></i>
                    <?= $n_grupos ?> grupo<?= $n_grupos !== 1 ? 's' : '' ?> configurado<?= $n_grupos !== 1 ? 's' : '' ?>
                </div>

                <div style="display:flex;gap:8px;margin-top:4px;">

                    <!-- Grupos Facebook -->
                    <a href="index.php?page=links&view=grupos&empresa=<?= $eid ?>"
                       style="flex:1;display:flex;align-items:center;justify-content:center;gap:7px;background:#0f2544;color:#66FCF1;border-radius:9px;padding:10px 14px;font-weight:700;font-size:.85rem;text-decoration:none;transition:background .2s;"
                       onmouseover="this.style.background='#1a3a6e'" onmouseout="this.style.background='#0f2544'">
                        <i class="bi bi-megaphone"></i> Publicar
                    </a>

                    <!-- Perfiles / Vacantes -->
                    <a href="index.php?page=perfiles&empresa=<?= $eid ?>"
                       style="flex:1;display:flex;align-items:center;justify-content:center;gap:7px;background:#f4f7fb;color:#0f2544;border-radius:9px;padding:10px 14px;font-weight:700;font-size:.85rem;text-decoration:none;border:1.5px solid #e2e8f0;transition:border-color .2s;"
                       onmouseover="this.style.borderColor='#0f2544'" onmouseout="this.style.borderColor='#e2e8f0'">
                        <i class="bi bi-file-earmark-person"></i> Perfiles
                    </a>

                </div>
            </div>
        </div>
    </div>
    <?php endwhile; ?>
</div>

<!-- Resumen de publicaciones del usuario -->
<?php
$resumen = $conexion->query("
    SELECT e.nombre as empresa, COUNT(*) as total,
           SUM(YEARWEEK(p.fecha,1) = YEARWEEK(NOW(),1)) as semana,
           SUM(MONTH(p.fecha) = MONTH(NOW()) AND YEAR(p.fecha) = YEAR(NOW())) as mes
    FROM publicaciones p
    JOIN empresas e ON e.id = p.empresa_id
    WHERE p.usuario_id = $usuario_id
    GROUP BY p.empresa_id
    ORDER BY total DESC
");

if ($usuario_rol === 'admin') {
    $resumen_admin = $conexion->query("
        SELECT u.nombre as usuario, COUNT(*) as total,
               SUM(YEARWEEK(p.fecha,1) = YEARWEEK(NOW(),1)) as semana,
               SUM(MONTH(p.fecha) = MONTH(NOW()) AND YEAR(p.fecha) = YEAR(NOW())) as mes
        FROM publicaciones p
        JOIN usuarios u ON u.id = p.usuario_id
        GROUP BY p.usuario_id
        ORDER BY total DESC
    ");
}
?>

<?php if ($resumen && $resumen->num_rows > 0): ?>
<div style="margin-top:36px;">
    <h5 style="font-weight:800;color:#0f2544;margin-bottom:4px;">Mis publicaciones</h5>
    <p style="color:#8896a7;font-size:.88rem;margin-bottom:16px;">Resumen por empresa.</p>
    <div class="row g-3">
        <?php while ($r = $resumen->fetch_assoc()): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="stat-card">
                <div style="font-size:.72rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($r['empresa']) ?>
                </div>
                <div style="font-size:2rem;font-weight:800;color:#0f2544;line-height:1;"><?= $r['total'] ?></div>
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                    <span style="display:inline-flex;align-items:center;gap:3px;font-size:.72rem;font-weight:600;padding:3px 8px;border-radius:50px;background:rgba(102,252,241,.12);color:#0f2544;">
                        <i class="bi bi-calendar-week"></i> <?= (int)$r['semana'] ?> sem.
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:3px;font-size:.72rem;font-weight:600;padding:3px 8px;border-radius:50px;background:rgba(15,37,68,.07);color:#0f2544;">
                        <i class="bi bi-calendar-month"></i> <?= (int)$r['mes'] ?> mes
                    </span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<?php if ($usuario_rol === 'admin' && isset($resumen_admin) && $resumen_admin && $resumen_admin->num_rows > 0): ?>
<div style="margin-top:36px;">
    <h5 style="font-weight:800;color:#0f2544;margin-bottom:4px;">Publicaciones del equipo</h5>
    <p style="color:#8896a7;font-size:.88rem;margin-bottom:16px;">Resumen por reclutador.</p>
    <div class="row g-3">
        <?php while ($r = $resumen_admin->fetch_assoc()): ?>
        <div class="col-sm-6 col-md-4 col-lg-3">
            <div class="stat-card">
                <div style="font-size:.72rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars($r['usuario']) ?>
                </div>
                <div style="font-size:2rem;font-weight:800;color:#0f2544;line-height:1;"><?= $r['total'] ?></div>
                <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
                    <span style="display:inline-flex;align-items:center;gap:3px;font-size:.72rem;font-weight:600;padding:3px 8px;border-radius:50px;background:rgba(102,252,241,.12);color:#0f2544;">
                        <i class="bi bi-calendar-week"></i> <?= (int)$r['semana'] ?> sem.
                    </span>
                    <span style="display:inline-flex;align-items:center;gap:3px;font-size:.72rem;font-weight:600;padding:3px 8px;border-radius:50px;background:rgba(15,37,68,.07);color:#0f2544;">
                        <i class="bi bi-calendar-month"></i> <?= (int)$r['mes'] ?> mes
                    </span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<?php else: ?>
<div class="panel-card mt-4">
    <div style="padding:60px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-folder2-open" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">No hay empresas configuradas aún.</p>
        <?php if ($usuario_rol === 'admin'): ?>
        <a href="index.php?page=gestionar_empresas" style="color:#0f2544;font-size:.88rem;font-weight:600;display:inline-block;margin-top:8px;">+ Agregar empresa</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php } // fin vista kit ?>
