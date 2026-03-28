<?php
require_once("../sistema/conexion.php");

// Solo admin
if ($_SESSION['rol'] !== 'admin') {
    header("Location: index.php?page=links");
    exit();
}

$msg      = "";
$msg_tipo = "";

// ── Acciones POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Crear empresa
    if ($accion === 'crear_empresa') {
        $nombre       = trim($_POST['nombre']);
        $perfiles_url = trim($_POST['perfiles_url'] ?? '');
        if (!empty($nombre)) {
            $stmt = $conexion->prepare("INSERT INTO empresas (nombre, perfiles_url) VALUES (?, ?)");
            $stmt->bind_param("ss", $nombre, $perfiles_url);
            $stmt->execute() ? ($msg = "Empresa creada.") : ($msg = "Error al crear.");
            $msg_tipo = $stmt->affected_rows > 0 ? "success" : "danger";
            $stmt->close();
        }
    }

    // Editar empresa
    if ($accion === 'editar_empresa') {
        $id           = (int)$_POST['empresa_id'];
        $nombre       = trim($_POST['nombre']);
        $perfiles_url = trim($_POST['perfiles_url'] ?? '');
        $activo       = isset($_POST['activo']) ? 1 : 0;
        $stmt = $conexion->prepare("UPDATE empresas SET nombre=?, perfiles_url=?, activo=? WHERE id=?");
        $stmt->bind_param("ssii", $nombre, $perfiles_url, $activo, $id);
        $stmt->execute();
        $msg = "Empresa actualizada."; $msg_tipo = "success";
        $stmt->close();
    }

    // Eliminar empresa
    if ($accion === 'eliminar_empresa') {
        $id = (int)$_POST['empresa_id'];
        $conexion->query("DELETE FROM empresas WHERE id = $id");
        $msg = "Empresa eliminada."; $msg_tipo = "success";
    }

    // Agregar link
    if ($accion === 'agregar_link') {
        $empresa_id = (int)$_POST['empresa_id'];
        $titulo     = trim($_POST['titulo']);
        $url        = trim($_POST['url']);
        if (!empty($titulo) && !empty($url)) {
            $stmt = $conexion->prepare("INSERT INTO empresa_links (empresa_id, titulo, url) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $empresa_id, $titulo, $url);
            $stmt->execute();
            $msg = "Link agregado."; $msg_tipo = "success";
            $stmt->close();
        }
    }

    // Eliminar link
    if ($accion === 'eliminar_link') {
        $id = (int)$_POST['link_id'];
        $conexion->query("DELETE FROM empresa_links WHERE id = $id");
        $msg = "Link eliminado."; $msg_tipo = "success";
    }
}

// ── Cargar empresas ──────────────────────────────────────────
$empresas = $conexion->query("SELECT * FROM empresas ORDER BY nombre ASC");
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;">Gestionar Empresas</h4>
        <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">Administra empresas, links e instrucciones del kit.</p>
    </div>
    <a href="index.php?page=links"
       style="background:#f4f7fb;color:#0f2544;border-radius:50px;padding:8px 20px;font-weight:600;font-size:.85rem;text-decoration:none;border:1.5px solid #e2e8f0;">
        <i class="bi bi-arrow-left me-1"></i>Volver al kit
    </a>
</div>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msg_tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;font-size:.88rem;">
    <i class="bi <?= $msg_tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- ── Formulario nueva empresa ──────────────────────────── -->
<div class="panel-card mb-4">
    <div class="panel-card-header">
        <h6><i class="bi bi-plus-circle me-2" style="color:#66FCF1;"></i>Nueva Empresa</h6>
    </div>
    <div style="padding:24px;">
        <form method="POST">
            <input type="hidden" name="accion" value="crear_empresa">
            <div class="row g-3">
                <div class="col-md-4">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:6px;">Nombre de la empresa</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Empresa Alpha" required
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-8">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:6px;">Link de Perfiles (Google Docs / Drive)</label>
                    <input type="url" name="perfiles_url" class="form-control" placeholder="https://docs.google.com/..."
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
            </div>
            <div class="mt-3">
                <button type="submit"
                        style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:10px 24px;font-weight:700;font-size:.88rem;cursor:pointer;">
                    <i class="bi bi-plus me-1"></i>Crear empresa
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Lista de empresas ─────────────────────────────────── -->
<?php if ($empresas && $empresas->num_rows > 0):
    while ($empresa = $empresas->fetch_assoc()):
        $eid   = (int)$empresa['id'];
        $links = $conexion->query("SELECT * FROM empresa_links WHERE empresa_id = $eid ORDER BY id ASC");
?>

<div class="panel-card mb-4">
    <div class="panel-card-header" style="background:#0f2544;">
        <h6 style="color:#66FCF1;margin:0;font-weight:700;">
            <i class="bi bi-building me-2"></i><?= htmlspecialchars($empresa['nombre']) ?>
        </h6>
        <span style="font-size:.75rem;padding:3px 10px;border-radius:50px;font-weight:600;
              background:<?= $empresa['activo'] ? 'rgba(102,252,241,.15)' : 'rgba(255,100,100,.15)' ?>;
              color:<?= $empresa['activo'] ? '#66FCF1' : '#ff8080' ?>;">
            <?= $empresa['activo'] ? 'Activa' : 'Inactiva' ?>
        </span>
    </div>

    <div style="padding:24px;">
        <div class="row g-4">

            <!-- Editar empresa -->
            <div class="col-lg-5">
                <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:10px;">Editar empresa</p>
                <form method="POST">
                    <input type="hidden" name="accion"     value="editar_empresa">
                    <input type="hidden" name="empresa_id" value="<?= $eid ?>">
                    <div class="mb-2">
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($empresa['nombre']) ?>" required
                               style="border-radius:9px;font-size:.87rem;border:1.5px solid #e2e8f0;">
                    </div>
                    <div class="mb-2">
                        <input type="url" name="perfiles_url" class="form-control"
                               value="<?= htmlspecialchars($empresa['perfiles_url'] ?? '') ?>"
                               placeholder="https://docs.google.com/..."
                               style="border-radius:9px;font-size:.87rem;border:1.5px solid #e2e8f0;">
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <label style="font-size:.83rem;color:#0f2544;display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="checkbox" name="activo" <?= $empresa['activo'] ? 'checked' : '' ?>>
                            Empresa activa
                        </label>
                        <button type="submit"
                                style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:8px 18px;font-weight:700;font-size:.83rem;cursor:pointer;">
                            <i class="bi bi-check2 me-1"></i>Guardar
                        </button>
                    </div>
                </form>

                <!-- Eliminar empresa -->
                <form method="POST" style="margin-top:10px;"
                      onsubmit="return confirm('¿Eliminar esta empresa y todos sus datos?')">
                    <input type="hidden" name="accion"     value="eliminar_empresa">
                    <input type="hidden" name="empresa_id" value="<?= $eid ?>">
                    <button type="submit"
                            style="background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.2);border-radius:9px;padding:7px 16px;font-weight:600;font-size:.82rem;cursor:pointer;width:100%;">
                        <i class="bi bi-trash me-1"></i>Eliminar empresa
                    </button>
                </form>
            </div>

            <!-- Links -->
            <div class="col-lg-7">
                <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:10px;">Links del grupo</p>

                <!-- Lista links existentes -->
                <?php if ($links && $links->num_rows > 0): ?>
                <div style="display:flex;flex-direction:column;gap:6px;margin-bottom:14px;">
                    <?php while ($lnk = $links->fetch_assoc()): ?>
                    <div style="display:flex;align-items:center;gap:8px;background:#f4f7fb;border-radius:9px;padding:9px 12px;border:1px solid #e2e8f0;">
                        <i class="bi bi-link-45deg" style="color:#66FCF1;flex-shrink:0;"></i>
                        <div style="flex:1;overflow:hidden;">
                            <div style="font-size:.85rem;font-weight:600;color:#0f2544;"><?= htmlspecialchars($lnk['titulo']) ?></div>
                            <div style="font-size:.76rem;color:#8896a7;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($lnk['url']) ?></div>
                        </div>
                        <form method="POST" onsubmit="return confirm('¿Eliminar este link?')" style="flex-shrink:0;">
                            <input type="hidden" name="accion"  value="eliminar_link">
                            <input type="hidden" name="link_id" value="<?= $lnk['id'] ?>">
                            <button type="submit" style="background:none;border:none;color:#dc3545;cursor:pointer;font-size:.9rem;padding:2px 6px;">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </form>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <!-- Agregar link -->
                <form method="POST">
                    <input type="hidden" name="accion"     value="agregar_link">
                    <input type="hidden" name="empresa_id" value="<?= $eid ?>">
                    <div class="mb-2">
                        <input type="text" name="titulo" class="form-control" placeholder="Título del link (ej. Grupo Facebook Alpha)"
                               required style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                    </div>
                    <div class="mb-2">
                        <input type="url" name="url" class="form-control" placeholder="https://facebook.com/groups/..."
                               required style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                    </div>
                    <button type="submit"
                            style="background:rgba(15,37,68,.08);color:#0f2544;border:1.5px solid #e2e8f0;border-radius:9px;padding:8px 18px;font-weight:700;font-size:.83rem;cursor:pointer;">
                        <i class="bi bi-plus me-1"></i>Agregar link
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php endwhile;
else: ?>
<div class="panel-card">
    <div style="padding:40px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-building" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">Aún no hay empresas. Crea la primera arriba.</p>
    </div>
</div>
<?php endif; ?>
