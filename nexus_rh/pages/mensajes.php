<?php
require_once("../sistema/conexion.php");

// Marcar como leído
if (isset($_GET['leer'])) {
    $id = (int)$_GET['leer'];
    $conexion->query("UPDATE contacto_mensajes SET leido=1 WHERE id=$id");
    header("Location: index.php?page=mensajes");
    exit();
}

// Eliminar
if (isset($_GET['eliminar'])) {
    $id = (int)$_GET['eliminar'];
    $conexion->query("DELETE FROM contacto_mensajes WHERE id=$id");
    header("Location: index.php?page=mensajes");
    exit();
}

$mensajes  = $conexion->query("SELECT * FROM contacto_mensajes ORDER BY created_at DESC");
$no_leidos = $conexion->query("SELECT COUNT(*) as c FROM contacto_mensajes WHERE leido=0")->fetch_assoc()['c'];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;">Mensajes de contacto</h4>
        <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">
            Mensajes recibidos desde la página pública.
            <?php if ($no_leidos > 0): ?>
            <span style="background:#e74c3c;color:#fff;font-size:.72rem;font-weight:700;padding:2px 8px;border-radius:50px;margin-left:4px;">
                <?= $no_leidos ?> nuevo<?= $no_leidos !== 1 ? 's' : '' ?>
            </span>
            <?php endif; ?>
        </p>
    </div>
</div>

<div class="panel-card">
    <?php if ($mensajes && $mensajes->num_rows > 0): ?>
    <div style="overflow-x:auto;">
        <table class="table table-hover mb-0" style="font-size:.86rem;">
            <thead style="background:#f4f7fb;">
                <tr>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Nombre</th>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Asunto</th>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Mensaje</th>
                    <th style="padding:12px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Fecha</th>
                    <th style="padding:12px 16px;border:none;"></th>
                </tr>
            </thead>
            <tbody>
            <?php while ($m = $mensajes->fetch_assoc()): ?>
            <tr style="<?= !$m['leido'] ? 'background:#fffbeb;' : '' ?>">
                <td style="padding:12px 16px;border-color:#f4f7fb;">
                    <div style="font-weight:<?= !$m['leido'] ? '700' : '500' ?>;color:#0f2544;">
                        <?= htmlspecialchars($m['nombre']) ?>
                        <?php if (!$m['leido']): ?>
                        <span style="display:inline-block;width:7px;height:7px;background:#e74c3c;border-radius:50%;margin-left:4px;vertical-align:middle;"></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($m['empresa'])): ?>
                    <div style="font-size:.76rem;color:#8896a7;"><?= htmlspecialchars($m['empresa']) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($m['email'])): ?>
                    <div style="font-size:.76rem;color:#8896a7;">
                        <a href="mailto:<?= htmlspecialchars($m['email']) ?>" style="color:#0f2544;"><?= htmlspecialchars($m['email']) ?></a>
                    </div>
                    <?php endif; ?>
                    <?php if (!empty($m['telefono'])): ?>
                    <div style="font-size:.76rem;color:#8896a7;"><?= htmlspecialchars($m['telefono']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;color:#4a5568;">
                    <?= htmlspecialchars($m['asunto'] ?: '—') ?>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;color:#4a5568;max-width:260px;">
                    <div style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                        <?= htmlspecialchars($m['mensaje']) ?>
                    </div>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;color:#8896a7;white-space:nowrap;font-size:.82rem;">
                    <?= date('d/m/Y H:i', strtotime($m['created_at'])) ?>
                </td>
                <td style="padding:12px 16px;border-color:#f4f7fb;white-space:nowrap;">
                    <div style="display:flex;gap:6px;">
                        <?php if (!$m['leido']): ?>
                        <a href="index.php?page=mensajes&leer=<?= $m['id'] ?>"
                           style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;background:#f4f7fb;border-radius:7px;border:1.5px solid #e2e8f0;text-decoration:none;color:#0f2544;font-size:.8rem;"
                           title="Marcar como leído">
                            <i class="bi bi-check2"></i>
                        </a>
                        <?php endif; ?>
                        <a href="index.php?page=mensajes&eliminar=<?= $m['id'] ?>"
                           onclick="return confirm('¿Eliminar este mensaje?')"
                           style="display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;background:rgba(220,53,69,.08);border-radius:7px;border:1px solid rgba(220,53,69,.2);text-decoration:none;color:#dc3545;font-size:.8rem;"
                           title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
    <div style="padding:60px 20px;text-align:center;color:#8896a7;">
        <i class="bi bi-envelope" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">No hay mensajes recibidos aún.</p>
    </div>
    <?php endif; ?>
</div>