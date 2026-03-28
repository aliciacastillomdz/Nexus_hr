<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: ../login.php");
    exit();
}

require_once("../sistema/conexion.php");

$usuario_id  = $_SESSION['id'];
$usuario_rol = $_SESSION['rol'];
$es_admin    = $usuario_rol === 'admin';

// ── Mismos filtros que solicitantes.php ──────────────────────
$filtro_estatus = $_GET['estatus'] ?? '';
$filtro_empresa = isset($_GET['empresa']) ? (int)$_GET['empresa'] : 0;
$filtro_buscar  = trim($_GET['buscar'] ?? '');

$where_parts = [];
if (!$es_admin) $where_parts[] = "s.usuario_id = $usuario_id";
if ($filtro_estatus) $where_parts[] = "s.estatus = '" . $conexion->real_escape_string($filtro_estatus) . "'";
if ($filtro_empresa) $where_parts[] = "s.empresa_id = $filtro_empresa";
if ($filtro_buscar)  $where_parts[] = "(s.nombre LIKE '%" . $conexion->real_escape_string($filtro_buscar) . "%' OR s.telefono LIKE '%" . $conexion->real_escape_string($filtro_buscar) . "%')";
$where_sql = $where_parts ? "WHERE " . implode(" AND ", $where_parts) : "";

$solicitantes = $conexion->query("
    SELECT
        s.nombre,
        s.edad,
        s.colonia,
        s.telefono,
        e.nombre   AS empresa,
        v.nombre   AS puesto,
        s.fecha_cita,
        s.estatus,
        s.infonavit_fonacot,
        s.descuento_monto,
        s.adeudo_santander,
        u.nombre   AS reclutador
    FROM solicitantes s
    LEFT JOIN empresas e  ON e.id = s.empresa_id
    LEFT JOIN vacantes v  ON v.id = s.vacante_id
    LEFT JOIN usuarios u  ON u.id = s.usuario_id
    $where_sql
    ORDER BY s.fecha_cita ASC, s.created_at DESC
");

// ── Nombre del archivo ───────────────────────────────────────
$fecha_hoy = date('d-m-Y');
$nombre_archivo = "Solicitantes_NexusRH_$fecha_hoy";
if ($filtro_estatus) $nombre_archivo .= "_" . str_replace(' ', '_', $filtro_estatus);
if ($filtro_empresa) {
    $emp_row = $conexion->query("SELECT nombre FROM empresas WHERE id=$filtro_empresa")->fetch_assoc();
    if ($emp_row) $nombre_archivo .= "_" . str_replace(' ', '_', $emp_row['nombre']);
}

// ── Headers CSV ──────────────────────────────────────────────
header("Content-Type: text/csv; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$nombre_archivo.csv\"");
header("Pragma: no-cache");
header("Expires: 0");

$out = fopen('php://output', 'w');

// BOM para que Excel abra UTF-8 correctamente
fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

// Función helper para escribir fila CSV limpia
function csv_row($handle, array $fields) {
    // Limpiar saltos de línea dentro de celdas
    $fields = array_map(fn($v) => str_replace(["\r\n", "\r", "\n"], ' ', (string)$v), $fields);
    fputcsv($handle, $fields, ',', '"');
}

// ── Metadata ─────────────────────────────────────────────────
csv_row($out, ['NEXUS RH SOLUCIONES']);
csv_row($out, ['Reporte de Solicitantes']);
csv_row($out, ['Fecha de exportacion', date('d/m/Y H:i')]);

$filtros_texto = [];
if ($filtro_estatus)          $filtros_texto[] = "Estatus: $filtro_estatus";
if ($filtro_empresa && isset($emp_row)) $filtros_texto[] = "Empresa: " . $emp_row['nombre'];
if ($filtro_buscar)           $filtros_texto[] = "Busqueda: $filtro_buscar";
if (!$es_admin)               $filtros_texto[] = "Reclutador: " . ($_SESSION['nombre'] ?? '');

csv_row($out, ['Filtros aplicados', count($filtros_texto) ? implode(' | ', $filtros_texto) : 'Ninguno']);
csv_row($out, ['Total de registros', $solicitantes ? $solicitantes->num_rows : 0]);
csv_row($out, []); // fila vacía separadora

// ── Encabezados de columnas ──────────────────────────────────
$columnas = [
    'Nombre completo',
    'Edad',
    'Colonia',
    'Telefono',
    'Empresa',
    'Puesto',
    'Fecha de cita',
    'Estatus',
    'Infonavit / Fonacot',
    'Monto descuento',
    'Adeudo Santander',
];
if ($es_admin) $columnas[] = 'Reclutador';
csv_row($out, $columnas);

// ── Datos ────────────────────────────────────────────────────
if ($solicitantes && $solicitantes->num_rows > 0) {
    while ($s = $solicitantes->fetch_assoc()) {
        $fila = [
            $s['nombre'],
            $s['edad'] ?? '',
            $s['colonia'] ?? '',
            $s['telefono'] ?? '',           // CSV maneja bien el texto, sin necesidad del '
            $s['empresa'] ?? '',
            $s['puesto']  ?? '',
            $s['fecha_cita'] ? date('d/m/Y', strtotime($s['fecha_cita'])) : '',
            $s['estatus'],
            $s['infonavit_fonacot'] ? 'Si' : 'No',
            $s['infonavit_fonacot'] ? ($s['descuento_monto'] ?? '') : '',
            $s['adeudo_santander']  ? 'Si' : 'No',
        ];
        if ($es_admin) $fila[] = $s['reclutador'];
        csv_row($out, $fila);
    }
} else {
    csv_row($out, ['Sin registros con los filtros aplicados.']);
}

fclose($out);
exit();