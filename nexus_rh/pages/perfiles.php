<?php
require_once("../sistema/conexion.php");

$usuario_rol = $_SESSION['rol'];

// Vista: lista de empresas o detalle de una empresa
$empresa_id = isset($_GET['empresa']) ? (int)$_GET['empresa'] : 0;

// ════════════════════════════════════════════════════════════
//  VISTA DETALLE — vacantes de una empresa
// ════════════════════════════════════════════════════════════
if ($empresa_id > 0) {

    $emp = $conexion->query("SELECT * FROM empresas WHERE id = $empresa_id AND activo = 1")->fetch_assoc();
    if (!$emp) { header("Location: index.php?page=perfiles"); exit(); }

    $vacantes = $conexion->query("SELECT * FROM vacantes WHERE empresa_id = $empresa_id AND activo = 1 ORDER BY id ASC");
?>

<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <a href="index.php?page=perfiles"
       style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:#f4f7fb;border-radius:9px;border:1.5px solid #e2e8f0;text-decoration:none;color:#0f2544;flex-shrink:0;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div style="flex:1;">
        <h4 style="font-weight:800;color:#0f2544;margin:0;"><?= htmlspecialchars($emp['nombre']) ?></h4>
        <p style="color:#8896a7;margin:2px 0 0;font-size:.86rem;">Vacantes disponibles y respuestas rápidas.</p>
    </div>
    <?php if ($usuario_rol === 'admin'): ?>
    <a href="index.php?page=gestionar_vacantes&empresa=<?= $empresa_id ?>"
       style="background:#0f2544;color:#66FCF1;border-radius:50px;padding:8px 20px;font-weight:700;font-size:.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;flex-shrink:0;">
        <i class="bi bi-gear"></i> Gestionar
    </a>
    <?php endif; ?>
</div>

<?php if ($vacantes && $vacantes->num_rows > 0):
    while ($v = $vacantes->fetch_assoc()):
        $vid = (int)$v['id'];
        $respuestas = $conexion->query("SELECT * FROM respuestas_rapidas WHERE vacante_id = $vid ORDER BY orden ASC, id ASC");
?>

<div class="panel-card mb-4">

    <!-- Header vacante -->
    <div class="panel-card-header" style="background:#0f2544;">
        <h6 style="color:#66FCF1;font-weight:700;margin:0;">
            <i class="bi bi-briefcase me-2"></i><?= htmlspecialchars($v['nombre']) ?>
        </h6>
    </div>

    <div style="padding:24px;">
        <div class="row g-4">

            <!-- Perfil del puesto -->
            <div class="col-lg-5">
                <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:14px;">Perfil del Puesto</p>

                <div style="display:flex;flex-direction:column;gap:12px;">

                    <?php if (!empty($v['descripcion'])): ?>
                    <div>
                        <div style="font-size:.72rem;font-weight:700;color:#0f2544;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Descripción</div>
                        <p style="font-size:.88rem;color:#4a5568;line-height:1.7;margin:0;white-space:pre-line;"><?= htmlspecialchars($v['descripcion']) ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if (!empty($v['requisitos'])): ?>
                    <div>
                        <div style="font-size:.72rem;font-weight:700;color:#0f2544;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Requisitos</div>
                        <p style="font-size:.88rem;color:#4a5568;line-height:1.7;margin:0;white-space:pre-line;"><?= htmlspecialchars($v['requisitos']) ?></p>
                    </div>
                    <?php endif; ?>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">

                        <?php if (!empty($v['sueldo'])): ?>
                        <div style="background:#f4f7fb;border-radius:9px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
                                <i class="bi bi-cash me-1"></i>Sueldo
                            </div>
                            <div style="font-size:.9rem;font-weight:700;color:#0f2544;"><?= htmlspecialchars($v['sueldo']) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($v['horario'])): ?>
                        <div style="background:#f4f7fb;border-radius:9px;padding:12px 14px;">
                            <div style="font-size:.68rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
                                <i class="bi bi-clock me-1"></i>Horario
                            </div>
                            <div style="font-size:.9rem;font-weight:700;color:#0f2544;"><?= htmlspecialchars($v['horario']) ?></div>
                        </div>
                        <?php endif; ?>

                        <?php if (!empty($v['ubicacion'])): ?>
                        <div style="background:#f4f7fb;border-radius:9px;padding:12px 14px;grid-column:1/-1;">
                            <div style="font-size:.68rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">
                                <i class="bi bi-geo-alt me-1"></i>Ubicación
                            </div>
                            <div style="font-size:.9rem;font-weight:700;color:#0f2544;"><?= htmlspecialchars($v['ubicacion']) ?></div>
                        </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <!-- Separador vertical -->
            <div class="col-lg-1 d-none d-lg-flex" style="justify-content:center;">
                <div style="width:1px;background:#e2e8f0;height:100%;"></div>
            </div>

            <!-- Respuestas rápidas -->
            <div class="col-lg-6">
                <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:14px;">Respuestas Rápidas</p>

                <?php if ($respuestas && $respuestas->num_rows > 0):
                    while ($r = $respuestas->fetch_assoc()):
                ?>
                <div style="margin-bottom:12px;">
                    <div style="font-size:.78rem;font-weight:700;color:#0f2544;margin-bottom:6px;">
                        <?= htmlspecialchars($r['titulo']) ?>
                    </div>
                    <div style="position:relative;">
                        <textarea readonly
                                  onclick="this.select();"
                                  style="width:100%;background:#f4f7fb;border:1.5px solid #e2e8f0;border-radius:9px;padding:10px 44px 10px 12px;font-size:.84rem;color:#4a5568;line-height:1.6;resize:none;cursor:pointer;font-family:inherit;"
                                  rows="<?= min(6, substr_count($r['contenido'], "\n") + 2) ?>"
                                  ><?= htmlspecialchars($r['contenido']) ?></textarea>
                        <!-- Botón copiar -->
                        <button onclick="copiarTexto(this, <?= $r['id'] ?>)"
                                data-texto="<?= htmlspecialchars($r['contenido'], ENT_QUOTES) ?>"
                                style="position:absolute;top:8px;right:8px;background:#0f2544;color:#66FCF1;border:none;border-radius:7px;padding:5px 9px;font-size:.75rem;cursor:pointer;transition:background .2s;"
                                onmouseover="this.style.background='#1a3a6e'" onmouseout="this.style.background='#0f2544'"
                                title="Copiar">
                            <i class="bi bi-clipboard" id="icon-<?= $r['id'] ?>"></i>
                        </button>
                    </div>
                </div>
                <?php endwhile; else: ?>
                <div style="text-align:center;padding:30px 0;color:#8896a7;">
                    <i class="bi bi-chat-text" style="font-size:2rem;opacity:.25;"></i>
                    <p style="margin-top:8px;font-size:.88rem;">Sin respuestas rápidas aún.</p>
                </div>
                <?php endif; ?>
            </div>

        </div>
    </div>
</div>

<?php endwhile; else: ?>
<div class="panel-card">
    <div style="padding:60px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-briefcase" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">No hay vacantes configuradas para esta empresa.</p>
        <?php if ($usuario_rol === 'admin'): ?>
        <a href="index.php?page=gestionar_vacantes&empresa=<?= $empresa_id ?>"
           style="color:#0f2544;font-size:.88rem;font-weight:600;display:inline-block;margin-top:8px;">+ Agregar vacante</a>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>

<?php
// ════════════════════════════════════════════════════════════
//  VISTA LISTA — todas las empresas
// ════════════════════════════════════════════════════════════
} else {
    $empresas = $conexion->query("SELECT * FROM empresas WHERE activo = 1 ORDER BY nombre ASC");
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;">Perfiles y Vacantes</h4>
        <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">Selecciona una empresa para ver sus vacantes y respuestas rápidas.</p>
    </div>
</div>

<?php if ($empresas && $empresas->num_rows > 0): ?>
<div class="row g-3">
    <?php while ($empresa = $empresas->fetch_assoc()):
        $eid      = (int)$empresa['id'];
        $n_vac    = (int)$conexion->query("SELECT COUNT(*) as c FROM vacantes WHERE empresa_id = $eid AND activo = 1")->fetch_assoc()['c'];
    ?>
    <div class="col-md-6 col-lg-4">
        <a href="index.php?page=perfiles&empresa=<?= $eid ?>" style="text-decoration:none;">
            <div class="panel-card h-100" style="transition:box-shadow .2s,transform .2s;cursor:pointer;"
                 onmouseover="this.style.boxShadow='0 8px 30px rgba(0,0,0,0.1)';this.style.transform='translateY(-3px)'"
                 onmouseout="this.style.boxShadow='';this.style.transform=''">

                <div style="background:#0f2544;padding:16px 20px;">
                    <h6 style="color:#66FCF1;font-weight:700;margin:0;font-size:.95rem;">
                        <i class="bi bi-building me-2"></i><?= htmlspecialchars($empresa['nombre']) ?>
                    </h6>
                </div>

                <div style="padding:16px 20px;display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.83rem;color:#8896a7;">
                        <i class="bi bi-briefcase me-1"></i><?= $n_vac ?> vacante<?= $n_vac !== 1 ? 's' : '' ?>
                    </span>
                    <span style="font-size:.82rem;font-weight:600;color:#0f2544;">
                        Ver perfiles <i class="bi bi-arrow-right ms-1"></i>
                    </span>
                </div>

            </div>
        </a>
    </div>
    <?php endwhile; ?>
</div>

<?php else: ?>
<div class="panel-card">
    <div style="padding:60px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-building" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">No hay empresas configuradas aún.</p>
    </div>
</div>
<?php endif; ?>

<?php } ?>

<!-- Script copiar al portapapeles -->
<script>
function copiarTexto(btn, id) {
    const texto = btn.getAttribute('data-texto');
    navigator.clipboard.writeText(texto).then(() => {
        const icon = document.getElementById('icon-' + id);
        icon.className = 'bi bi-clipboard-check';
        btn.style.background = '#00c8a0';
        btn.style.color = '#0d1117';
        setTimeout(() => {
            icon.className = 'bi bi-clipboard';
            btn.style.background = '#0f2544';
            btn.style.color = '#66FCF1';
        }, 2000);
    });
}
</script>
