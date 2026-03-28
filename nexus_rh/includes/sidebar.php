<?php
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';

function isActive($name) {
    global $page;
    return $page === $name ? 'active' : '';
}
?>

<aside class="sidebar">

    <div class="sidebar-header">
        <div class="sidebar-logo">
            Nexus RH
            <small>Panel Administrativo</small>
        </div>
    </div>

    <nav class="sidebar-nav">

        <div class="sidebar-label">Principal</div>

        <a href="index.php?page=dashboard" class="<?= isActive('dashboard') ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <a href="index.php?page=empleados" class="<?= isActive('empleados') ?>">
            <i class="bi bi-people"></i> Empleados
        </a>

        <div class="sidebar-label">Herramientas</div>

        <a href="index.php?page=links" class="<?= isActive('links') ?>">
            <i class="bi bi-link-45deg"></i> Kit de Documentos
        </a>

        <a href="index.php?page=perfiles" class="<?= (isset($_GET['page']) && in_array($_GET['page'], ['perfiles','gestionar_vacantes'])) ? 'active' : '' ?>">
            <i class="bi bi-briefcase"></i> Perfiles y Vacantes
        </a>

        <a href="index.php?page=solicitantes" class="<?= (isset($_GET['page']) && $_GET['page'] === 'solicitantes') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> Solicitantes
        </a>

        <a href="index.php?page=mensajes" class="<?= isActive('mensajes') ?>">
            <i class="bi bi-envelope"></i> Mensajes
        </a>

        <a href="index.php?page=configuracion" class="<?= isActive('configuracion') ?>">
            <i class="bi bi-bar-chart-line"></i> Estadísticas
        </a>

        <div class="sidebar-label">Sistema</div>

        <a href="index.php?page=registrar" class="<?= isActive('registrar') ?>">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </a>

        <a href="../logout.php" class="logout">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">
                <?= strtoupper(substr($_SESSION['nombre'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="sidebar-user-info">
                <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Usuario') ?></div>
                <div class="sidebar-user-role"><?= htmlspecialchars($_SESSION['rol'] ?? '') ?></div>
            </div>
        </div>
    </div>

</aside>