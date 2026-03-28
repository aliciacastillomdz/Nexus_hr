<?php
require_once("../sistema/conexion.php");

$usuario_id  = $_SESSION['id'];
$usuario_rol = $_SESSION['rol'];
$es_admin    = $usuario_rol === 'admin';

// ── Filtro de reclutador (solo admin puede cambiar) ──────────
$filtro_uid = $es_admin && isset($_GET['reclutador']) ? (int)$_GET['reclutador'] : $usuario_id;
$scope      = $es_admin && $filtro_uid === 0 ? "" : "WHERE s.usuario_id = $filtro_uid";
$scope_and  = $es_admin && $filtro_uid === 0 ? "WHERE" : "AND";

// ── TARJETAS RESUMEN ─────────────────────────────────────────
$total        = $conexion->query("SELECT COUNT(*) as c FROM solicitantes s $scope")->fetch_assoc()['c'];
$contratados  = $conexion->query("SELECT COUNT(*) as c FROM solicitantes s $scope " . ($scope ? "AND" : "WHERE") . " s.estatus = 'Contratado'")->fetch_assoc()['c'];
$no_continuo  = $conexion->query("SELECT COUNT(*) as c FROM solicitantes s $scope " . ($scope ? "AND" : "WHERE") . " s.estatus = 'No continuó'")->fetch_assoc()['c'];
$agendadas    = $conexion->query("SELECT COUNT(*) as c FROM solicitantes s $scope " . ($scope ? "AND" : "WHERE") . " s.estatus = 'Entrevista agendada'")->fetch_assoc()['c'];
$hoy          = date('Y-m-d');
$citas_hoy    = $conexion->query("SELECT COUNT(*) as c FROM solicitantes s $scope " . ($scope ? "AND" : "WHERE") . " s.fecha_cita = '$hoy'")->fetch_assoc()['c'];
$esta_semana  = $conexion->query("SELECT COUNT(*) as c FROM solicitantes s $scope " . ($scope ? "AND" : "WHERE") . " YEARWEEK(s.fecha_cita,1) = YEARWEEK(NOW(),1)")->fetch_assoc()['c'];

$tasa_contratacion = $total > 0 ? round(($contratados / $total) * 100) : 0;

// ── SOLICITANTES POR EMPRESA ─────────────────────────────────
$por_empresa_sql = "SELECT e.nombre, COUNT(*) as total,
    SUM(s.estatus = 'Contratado') as contratados,
    SUM(s.estatus = 'No continuó') as no_continuo
    FROM solicitantes s
    LEFT JOIN empresas e ON e.id = s.empresa_id
    " . ($scope ?: "WHERE 1") . ($scope ? " AND" : " AND") . " s.empresa_id IS NOT NULL
    GROUP BY s.empresa_id ORDER BY total DESC";
// rebuild cleanly
if ($es_admin && $filtro_uid === 0) {
    $por_empresa_q = $conexion->query("SELECT e.nombre, COUNT(*) as total, SUM(s.estatus='Contratado') as contratados, SUM(s.estatus='No continuó') as no_continuo FROM solicitantes s LEFT JOIN empresas e ON e.id=s.empresa_id WHERE s.empresa_id IS NOT NULL GROUP BY s.empresa_id ORDER BY total DESC");
} else {
    $por_empresa_q = $conexion->query("SELECT e.nombre, COUNT(*) as total, SUM(s.estatus='Contratado') as contratados, SUM(s.estatus='No continuó') as no_continuo FROM solicitantes s LEFT JOIN empresas e ON e.id=s.empresa_id WHERE s.usuario_id=$filtro_uid AND s.empresa_id IS NOT NULL GROUP BY s.empresa_id ORDER BY total DESC");
}
$por_empresa = [];
while ($r = $por_empresa_q->fetch_assoc()) $por_empresa[] = $r;

// ── SOLICITANTES POR MES (últimos 6 meses) ───────────────────
if ($es_admin && $filtro_uid === 0) {
    $por_mes_q = $conexion->query("SELECT DATE_FORMAT(created_at,'%b %Y') as mes, DATE_FORMAT(created_at,'%Y-%m') as mes_key, COUNT(*) as total FROM solicitantes WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mes_key ORDER BY mes_key ASC");
} else {
    $por_mes_q = $conexion->query("SELECT DATE_FORMAT(created_at,'%b %Y') as mes, DATE_FORMAT(created_at,'%Y-%m') as mes_key, COUNT(*) as total FROM solicitantes WHERE usuario_id=$filtro_uid AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY mes_key ORDER BY mes_key ASC");
}
$por_mes = [];
while ($r = $por_mes_q->fetch_assoc()) $por_mes[] = $r;
$max_mes = max(array_column($por_mes, 'total') ?: [1]);

// ── CITAS POR DÍA esta semana ────────────────────────────────
$dias_semana = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
$citas_semana = [];
for ($i = 0; $i < 7; $i++) {
    $fecha = date('Y-m-d', strtotime("monday this week +{$i} days"));
    if ($es_admin && $filtro_uid === 0) {
        $c = $conexion->query("SELECT COUNT(*) as c FROM solicitantes WHERE fecha_cita='$fecha'")->fetch_assoc()['c'];
    } else {
        $c = $conexion->query("SELECT COUNT(*) as c FROM solicitantes WHERE usuario_id=$filtro_uid AND fecha_cita='$fecha'")->fetch_assoc()['c'];
    }
    $citas_semana[] = ['dia' => $dias_semana[$i], 'fecha' => $fecha, 'total' => (int)$c];
}
$max_citas = max(array_column($citas_semana, 'total') ?: [1]);

// ── LISTA RECLUTADORES (admin) ───────────────────────────────
$reclutadores = $es_admin ? $conexion->query("SELECT id, nombre FROM usuarios ORDER BY nombre ASC") : null;
?>

<!-- Encabezado -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 style="font-weight:800;color:#0f2544;margin:0;">Estadísticas</h4>
        <p style="color:#8896a7;margin:4px 0 0;font-size:.88rem;">
            <?= $es_admin ? 'Métricas generales del equipo de reclutamiento.' : 'Tus métricas de reclutamiento.' ?>
        </p>
    </div>

    <!-- Filtro reclutador (solo admin) -->
    <?php if ($es_admin): ?>
    <form method="GET" style="display:flex;gap:8px;align-items:center;">
        <input type="hidden" name="page" value="configuracion">
        <select name="reclutador" class="form-select" onchange="this.form.submit()"
                style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;max-width:200px;">
            <option value="0" <?= $filtro_uid === 0 ? 'selected' : '' ?>>Todo el equipo</option>
            <?php while ($r = $reclutadores->fetch_assoc()): ?>
            <option value="<?= $r['id'] ?>" <?= $filtro_uid === $r['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($r['nombre']) ?>
            </option>
            <?php endwhile; ?>
        </select>
    </form>
    <?php endif; ?>
</div>

<!-- ── TARJETAS RESUMEN ──────────────────────────────────────── -->
<div class="row g-3 mb-4">

    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div style="font-size:.7rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Total</div>
            <div style="font-size:2.2rem;font-weight:800;color:#0f2544;line-height:1;"><?= $total ?></div>
            <div style="font-size:.75rem;color:#8896a7;margin-top:4px;">solicitantes</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="background:rgba(102,252,241,.06);border:1.5px solid rgba(102,252,241,.2);">
            <div style="font-size:.7rem;font-weight:700;color:#0f6b62;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Contratados</div>
            <div style="font-size:2.2rem;font-weight:800;color:#0f6b62;line-height:1;"><?= $contratados ?></div>
            <div style="font-size:.75rem;color:#0f6b62;margin-top:4px;">tasa <?= $tasa_contratacion ?>%</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="background:#fdf2f2;border:1.5px solid rgba(220,53,69,.15);">
            <div style="font-size:.7rem;font-weight:700;color:#922b21;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">No continuó</div>
            <div style="font-size:2.2rem;font-weight:800;color:#922b21;line-height:1;"><?= $no_continuo ?></div>
            <div style="font-size:.75rem;color:#922b21;margin-top:4px;">candidatos</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="background:#fef9e7;border:1.5px solid rgba(241,196,15,.3);">
            <div style="font-size:.7rem;font-weight:700;color:#9a7d0a;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">En entrevista</div>
            <div style="font-size:2.2rem;font-weight:800;color:#9a7d0a;line-height:1;"><?= $agendadas ?></div>
            <div style="font-size:.75rem;color:#9a7d0a;margin-top:4px;">agendadas</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card" style="background:#e8f4fd;border:1.5px solid rgba(52,152,219,.2);">
            <div style="font-size:.7rem;font-weight:700;color:#1a6fa8;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Citas hoy</div>
            <div style="font-size:2.2rem;font-weight:800;color:#1a6fa8;line-height:1;"><?= $citas_hoy ?></div>
            <div style="font-size:.75rem;color:#1a6fa8;margin-top:4px;"><?= date('d/m/Y') ?></div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-lg-2">
        <div class="stat-card">
            <div style="font-size:.7rem;font-weight:700;color:#8896a7;text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Esta semana</div>
            <div style="font-size:2.2rem;font-weight:800;color:#0f2544;line-height:1;"><?= $esta_semana ?></div>
            <div style="font-size:.75rem;color:#8896a7;margin-top:4px;">citas</div>
        </div>
    </div>

</div>

<div class="row g-4">

    <!-- ── CITAS POR DÍA esta semana ──────────────────────────── -->
    <div class="col-lg-6">
        <div class="panel-card h-100">
            <div class="panel-card-header">
                <h6>Citas esta semana</h6>
                <span style="font-size:.78rem;color:#8896a7;"><?= $esta_semana ?> total</span>
            </div>
            <div style="padding:20px 24px;">
                <?php foreach ($citas_semana as $d):
                    $pct     = $max_citas > 0 ? round(($d['total'] / $max_citas) * 100) : 0;
                    $es_hoy2 = $d['fecha'] === $hoy;
                ?>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:32px;font-size:.78rem;font-weight:700;color:<?= $es_hoy2 ? '#0f2544' : '#8896a7' ?>;text-align:right;flex-shrink:0;">
                        <?= $d['dia'] ?>
                    </div>
                    <div style="flex:1;background:#f4f7fb;border-radius:50px;height:10px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;height:100%;background:<?= $es_hoy2 ? '#66FCF1' : '#0f2544' ?>;border-radius:50px;transition:width .4s;"></div>
                    </div>
                    <div style="width:24px;font-size:.82rem;font-weight:800;color:<?= $es_hoy2 ? '#0f2544' : '#8896a7' ?>;flex-shrink:0;">
                        <?= $d['total'] ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ── SOLICITANTES POR MES ───────────────────────────────── -->
    <div class="col-lg-6">
        <div class="panel-card h-100">
            <div class="panel-card-header">
                <h6>Solicitantes por mes</h6>
                <span style="font-size:.78rem;color:#8896a7;">Últimos 6 meses</span>
            </div>
            <div style="padding:20px 24px;">
                <?php if (count($por_mes) > 0):
                    foreach ($por_mes as $m):
                        $pct = $max_mes > 0 ? round(($m['total'] / $max_mes) * 100) : 0;
                ?>
                <div style="display:flex;align-items:center;gap:12px;margin-bottom:12px;">
                    <div style="width:64px;font-size:.78rem;font-weight:700;color:#8896a7;text-align:right;flex-shrink:0;white-space:nowrap;">
                        <?= $m['mes'] ?>
                    </div>
                    <div style="flex:1;background:#f4f7fb;border-radius:50px;height:10px;overflow:hidden;">
                        <div style="width:<?= $pct ?>%;height:100%;background:#0f2544;border-radius:50px;"></div>
                    </div>
                    <div style="width:24px;font-size:.82rem;font-weight:800;color:#0f2544;flex-shrink:0;">
                        <?= $m['total'] ?>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div style="text-align:center;padding:40px 0;color:#8896a7;">
                    <i class="bi bi-calendar3" style="font-size:2rem;opacity:.25;"></i>
                    <p class="mt-2 mb-0" style="font-size:.88rem;">Sin datos aún.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── SOLICITANTES POR EMPRESA ──────────────────────────── -->
    <div class="col-12">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Solicitantes por empresa</h6>
                <span style="font-size:.78rem;color:#8896a7;"><?= count($por_empresa) ?> empresas</span>
            </div>
            <?php if (count($por_empresa) > 0): ?>
            <div style="padding:20px 24px;">
                <?php
                $max_emp = max(array_column($por_empresa, 'total') ?: [1]);
                foreach ($por_empresa as $emp):
                    $pct_total = round(($emp['total'] / $max_emp) * 100);
                    $pct_cont  = $emp['total'] > 0 ? round(($emp['contratados'] / $emp['total']) * 100) : 0;
                    $pct_no    = $emp['total'] > 0 ? round(($emp['no_continuo']  / $emp['total']) * 100) : 0;
                ?>
                <div style="margin-bottom:20px;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                        <span style="font-size:.88rem;font-weight:700;color:#0f2544;"><?= htmlspecialchars($emp['nombre']) ?></span>
                        <span style="font-size:.82rem;color:#8896a7;"><?= $emp['total'] ?> solicitantes</span>
                    </div>
                    <!-- Barra principal -->
                    <div style="background:#f4f7fb;border-radius:50px;height:12px;overflow:hidden;margin-bottom:6px;">
                        <div style="width:<?= $pct_total ?>%;height:100%;background:#0f2544;border-radius:50px;"></div>
                    </div>
                    <!-- Mini stats -->
                    <div style="display:flex;gap:16px;">
                        <span style="font-size:.74rem;font-weight:600;color:#0f6b62;">
                            <i class="bi bi-check-circle-fill me-1"></i><?= $emp['contratados'] ?> contratados (<?= $pct_cont ?>%)
                        </span>
                        <span style="font-size:.74rem;font-weight:600;color:#922b21;">
                            <i class="bi bi-x-circle-fill me-1"></i><?= $emp['no_continuo'] ?> no continuaron (<?= $pct_no ?>%)
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="padding:50px 20px;text-align:center;color:#8896a7;">
                <i class="bi bi-building" style="font-size:2.5rem;opacity:.25;"></i>
                <p class="mt-3 mb-0" style="font-size:.88rem;">Sin datos de empresas aún.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ── CONTRATADOS VS NO CONTINUARON ─────────────────────── -->
    <div class="col-lg-5">
        <div class="panel-card h-100">
            <div class="panel-card-header">
                <h6>Contratados vs No continuaron</h6>
            </div>
            <div style="padding:24px;">
                <?php
                $procesados = $contratados + $no_continuo;
                $pct_c = $procesados > 0 ? round(($contratados / $procesados) * 100) : 0;
                $pct_n = $procesados > 0 ? round(($no_continuo  / $procesados) * 100) : 0;
                ?>
                <?php if ($procesados > 0): ?>

                <!-- Barra proporcional -->
                <div style="display:flex;border-radius:50px;overflow:hidden;height:18px;margin-bottom:20px;">
                    <?php if ($pct_c > 0): ?>
                    <div style="width:<?= $pct_c ?>%;background:#66FCF1;"></div>
                    <?php endif; ?>
                    <?php if ($pct_n > 0): ?>
                    <div style="width:<?= $pct_n ?>%;background:#e74c3c;"></div>
                    <?php endif; ?>
                </div>

                <div style="display:flex;gap:16px;">
                    <div style="flex:1;text-align:center;background:rgba(102,252,241,.1);border-radius:10px;padding:16px;">
                        <div style="font-size:2rem;font-weight:800;color:#0f6b62;"><?= $contratados ?></div>
                        <div style="font-size:.75rem;font-weight:700;color:#0f6b62;margin-top:2px;">Contratados</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#0f6b62;"><?= $pct_c ?>%</div>
                    </div>
                    <div style="flex:1;text-align:center;background:#fdf2f2;border-radius:10px;padding:16px;">
                        <div style="font-size:2rem;font-weight:800;color:#922b21;"><?= $no_continuo ?></div>
                        <div style="font-size:.75rem;font-weight:700;color:#922b21;margin-top:2px;">No continuaron</div>
                        <div style="font-size:1.1rem;font-weight:800;color:#922b21;"><?= $pct_n ?>%</div>
                    </div>
                </div>

                <div style="text-align:center;margin-top:14px;font-size:.8rem;color:#8896a7;">
                    De <?= $procesados ?> candidatos procesados
                </div>

                <?php else: ?>
                <div style="text-align:center;padding:30px 0;color:#8896a7;">
                    <i class="bi bi-pie-chart" style="font-size:2rem;opacity:.25;"></i>
                    <p class="mt-2 mb-0" style="font-size:.88rem;">Sin datos aún.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── PRÓXIMAS CITAS ─────────────────────────────────────── -->
    <div class="col-lg-7">
        <div class="panel-card h-100">
            <div class="panel-card-header">
                <h6>Próximas citas</h6>
                <span style="font-size:.78rem;color:#8896a7;">Siguientes 7 días</span>
            </div>
            <?php
            if ($es_admin && $filtro_uid === 0) {
                $proximas = $conexion->query("SELECT s.nombre, s.telefono, s.fecha_cita, e.nombre as empresa, v.nombre as vacante, u.nombre as reclutador FROM solicitantes s LEFT JOIN empresas e ON e.id=s.empresa_id LEFT JOIN vacantes v ON v.id=s.vacante_id LEFT JOIN usuarios u ON u.id=s.usuario_id WHERE s.fecha_cita BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY s.fecha_cita ASC LIMIT 10");
            } else {
                $proximas = $conexion->query("SELECT s.nombre, s.telefono, s.fecha_cita, e.nombre as empresa, v.nombre as vacante FROM solicitantes s LEFT JOIN empresas e ON e.id=s.empresa_id LEFT JOIN vacantes v ON v.id=s.vacante_id WHERE s.usuario_id=$filtro_uid AND s.fecha_cita BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY) ORDER BY s.fecha_cita ASC LIMIT 10");
            }
            ?>
            <?php if ($proximas && $proximas->num_rows > 0): ?>
            <div style="overflow-x:auto;">
                <table class="table table-hover mb-0" style="font-size:.85rem;">
                    <thead style="background:#f4f7fb;">
                        <tr>
                            <th style="padding:10px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Candidato</th>
                            <th style="padding:10px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Empresa / Puesto</th>
                            <th style="padding:10px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Fecha</th>
                            <?php if ($es_admin && $filtro_uid === 0): ?>
                            <th style="padding:10px 16px;color:#8896a7;font-weight:600;font-size:.72rem;text-transform:uppercase;letter-spacing:.8px;border:none;">Reclutador</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($p = $proximas->fetch_assoc()):
                        $es_hoy3 = $p['fecha_cita'] === $hoy;
                    ?>
                    <tr style="<?= $es_hoy3 ? 'background:#fffbeb;' : '' ?>">
                        <td style="padding:10px 16px;border-color:#f4f7fb;">
                            <div style="font-weight:700;color:#0f2544;"><?= htmlspecialchars($p['nombre']) ?></div>
                            <div style="font-size:.76rem;color:#8896a7;"><?= htmlspecialchars($p['telefono'] ?? '') ?></div>
                        </td>
                        <td style="padding:10px 16px;border-color:#f4f7fb;">
                            <div style="font-size:.85rem;color:#0f2544;"><?= htmlspecialchars($p['empresa'] ?? '—') ?></div>
                            <div style="font-size:.76rem;color:#8896a7;"><?= htmlspecialchars($p['vacante'] ?? '') ?></div>
                        </td>
                        <td style="padding:10px 16px;border-color:#f4f7fb;white-space:nowrap;">
                            <?php if ($es_hoy3): ?>
                            <span style="font-size:.8rem;font-weight:700;color:#9a7d0a;display:flex;align-items:center;gap:4px;">
                                <i class="bi bi-calendar-check-fill"></i> Hoy
                            </span>
                            <?php else: ?>
                            <span style="font-size:.84rem;color:#4a5568;"><?= date('d/m/Y', strtotime($p['fecha_cita'])) ?></span>
                            <?php endif; ?>
                        </td>
                        <?php if ($es_admin && $filtro_uid === 0): ?>
                        <td style="padding:10px 16px;border-color:#f4f7fb;color:#8896a7;font-size:.83rem;">
                            <?= htmlspecialchars($p['reclutador']) ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding:40px 20px;text-align:center;color:#8896a7;">
                <i class="bi bi-calendar3" style="font-size:2.5rem;opacity:.25;"></i>
                <p class="mt-3 mb-0" style="font-size:.88rem;">Sin citas en los próximos 7 días.</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>