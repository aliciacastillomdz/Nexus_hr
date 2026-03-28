<?php
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode([]);
    exit();
}

require_once("../sistema/conexion.php");

$empresa_id = isset($_GET['empresa']) ? (int)$_GET['empresa'] : 0;

if ($empresa_id > 0) {
    $rows = $conexion->query("SELECT id, nombre FROM vacantes WHERE empresa_id = $empresa_id AND activo = 1 ORDER BY nombre ASC");
    $out = [];
    while ($r = $rows->fetch_assoc()) $out[] = $r;
    header('Content-Type: application/json');
    echo json_encode($out);
} else {
    echo json_encode([]);
}
exit();