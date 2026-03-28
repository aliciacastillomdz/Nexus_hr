<?php
session_start();

if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

// Sanitizar page para evitar path traversal
$page = isset($_GET['page']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['page']) : 'dashboard';

$pageTitles = [
    'dashboard'          => 'Dashboard',
    'empleados'          => 'Empleados',
    'links'              => 'Kit de Documentos',
    'gestionar_empresas' => 'Gestionar Empresas',
    'perfiles'           => 'Perfiles y Vacantes',
    'gestionar_vacantes' => 'Gestionar Vacantes',
    'solicitantes'       => 'Solicitantes',
    'mensajes'           => 'Mensajes',
    'configuracion'      => 'Estadísticas',
    'registrar'          => 'Nuevo Usuario',
];

$pageTitle = $pageTitles[$page] ?? 'Panel';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> | Nexus RH</title>
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body class="panel-body">

<?php include("../includes/sidebar.php"); ?>

<div class="panel-main-content">

    <!-- Topbar -->
    <div class="panel-topbar">
        <span class="topbar-title"><?= $pageTitle ?></span>
        <span class="topbar-date">
            <i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?>
        </span>
    </div>

    <!-- Contenido -->
    <div class="page-content">
        <?php
        $archivo = "../pages/" . $page . ".php";
        if (file_exists($archivo)) {
            include($archivo);
        } else {
            echo '
            <div class="text-center py-5" style="color:#8896a7;">
                <i class="bi bi-exclamation-circle" style="font-size:3rem;opacity:.3;"></i>
                <h5 class="mt-3" style="color:#0f2544;">Página no encontrada</h5>
                <p style="font-size:.9rem;">La sección <b>' . htmlspecialchars($page) . '</b> no existe.</p>
            </div>';
        }
        ?>
    </div>

</div>

<script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>