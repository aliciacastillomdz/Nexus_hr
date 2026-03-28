<?php
// Aquí puedes conectar queries reales cuando la BD esté lista
$stats = [
    ['label' => 'Empleados Activos',    'value' => '—', 'icon' => 'bi-people-fill',  'color' => '#3b82f6', 'bg' => 'rgba(59,130,246,0.1)'],
    ['label' => 'Empresas Registradas', 'value' => '—', 'icon' => 'bi-building',      'color' => '#00c8a0', 'bg' => 'rgba(0,200,160,0.1)'],
    ['label' => 'Links Publicados',     'value' => '—', 'icon' => 'bi-link-45deg',    'color' => '#f59e0b', 'bg' => 'rgba(245,158,11,0.1)'],
    ['label' => 'Usuarios del Sistema', 'value' => '—', 'icon' => 'bi-person-badge',  'color' => '#8b5cf6', 'bg' => 'rgba(139,92,246,0.1)'],
];
?>

<div class="mb-4">
    <h4 style="font-weight:800;color:#0f2544;margin:0;">
        Bienvenido, <?= htmlspecialchars($_SESSION['nombre']) ?> 👋
    </h4>
    <p style="color:#8896a7;margin:4px 0 0;font-size:.9rem;">
        Aquí tienes un resumen del estado actual del sistema.
    </p>
</div>

<!-- Stats -->
<div class="row g-3 mb-4">
    <?php foreach ($stats as $s): ?>
    <div class="col-sm-6 col-xl-3">
        <div class="stat-card d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:<?= $s['bg'] ?>;color:<?= $s['color'] ?>;">
                <i class="bi <?= $s['icon'] ?>"></i>
            </div>
            <div>
                <div class="stat-label"><?= $s['label'] ?></div>
                <div class="stat-value"><?= $s['value'] ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Accesos rápidos + Info -->
<div class="row g-3">
    <div class="col-md-6">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Accesos Rápidos</h6>
            </div>
            <div style="padding:20px;display:flex;flex-direction:column;gap:10px;">
                <a href="index.php?page=empleados" class="btn btn-outline-primary btn-sm text-start">
                    <i class="bi bi-people me-2"></i>Ver Empleados
                </a>
                <a href="index.php?page=links" class="btn btn-outline-success btn-sm text-start">
                    <i class="bi bi-link-45deg me-2"></i>Kit de Documentos
                </a>
                <a href="index.php?page=registrar" class="btn btn-outline-secondary btn-sm text-start">
                    <i class="bi bi-person-plus me-2"></i>Registrar Usuario
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="panel-card">
            <div class="panel-card-header">
                <h6>Información de Sesión</h6>
            </div>
            <div style="padding:20px;">
                <table class="table table-sm table-borderless mb-0" style="font-size:.87rem;">
                    <tr>
                        <td style="color:#8896a7;width:40%;">Usuario</td>
                        <td class="fw-semibold"><?= htmlspecialchars($_SESSION['nombre']) ?></td>
                    </tr>
                    <tr>
                        <td style="color:#8896a7;">Rol</td>
                        <td>
                            <span class="badge" style="background:rgba(0,200,160,.13);color:#00c8a0;font-size:.75rem;">
                                <?= htmlspecialchars($_SESSION['rol']) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="color:#8896a7;">Fecha</td>
                        <td><?= date('d/m/Y H:i') ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
