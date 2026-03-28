<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacantes — Nexus RH Soluciones</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        #mapa-vacantes {
            height: 420px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            z-index: 1;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 10px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .popup-vacante h6 {
            font-weight: 700;
            color: #0f2544;
            margin: 0 0 4px;
            font-size: .9rem;
        }
        .popup-vacante p {
            color: #8896a7;
            font-size: .8rem;
            margin: 0;
        }
        .popup-vacante .badge-zona {
            display: inline-block;
            background: rgba(15,37,68,.08);
            color: #0f2544;
            font-size: .72rem;
            font-weight: 600;
            padding: 2px 8px;
            border-radius: 50px;
            margin-top: 6px;
        }
    </style>
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="logo">Nexus RH</div>
    <button class="nav-toggle" id="navToggle" aria-label="Menú">
        <i class="bi bi-list"></i>
    </button>
    <nav id="mainNav">
        <a href="index.php#inicio">Inicio</a>
        <a href="index.php#nosotros">Nosotros</a>
        <a href="index.php#servicios">Servicios</a>
        <a href="index.php#contacto">Contacto</a>
        <a href="vacantes.php" class="btn-vacantes active">Vacantes</a>
        <a href="login.php" class="btn-login">Ingresar</a>
    </nav>
</header>

<?php
require_once("sistema/conexion.php");

// ── Geocodificar con Nominatim si faltan coordenadas ─────────
function geocodificar($ubicacion, $conexion, $vacante_id) {
    if (empty(trim($ubicacion))) return null;

    $query = urlencode($ubicacion . ', México');
    $url   = "https://nominatim.openstreetmap.org/search?q=$query&format=json&limit=1";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_USERAGENT      => 'NexusRH/1.0',
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    $response = curl_exec($ch);
    curl_close($ch);

    if (!$response) return null;

    $data = json_decode($response, true);
    if (empty($data)) return null;

    $lat = (float)$data[0]['lat'];
    $lng = (float)$data[0]['lon'];

    // Guardar en BD para no volver a geocodificar
    $conexion->query("UPDATE vacantes SET lat=$lat, lng=$lng WHERE id=$vacante_id");

    return ['lat' => $lat, 'lng' => $lng];
}

// ── Filtros ──────────────────────────────────────────────────
$zona_filtro = trim($_GET['zona'] ?? '');
$buscar      = trim($_GET['buscar'] ?? '');

$zonas_q = $conexion->query("SELECT DISTINCT ubicacion FROM vacantes WHERE activo=1 AND ubicacion IS NOT NULL AND ubicacion != '' ORDER BY ubicacion ASC");
$zonas = [];
while ($z = $zonas_q->fetch_assoc()) $zonas[] = $z['ubicacion'];

$where = ["v.activo = 1"];
if ($zona_filtro) $where[] = "v.ubicacion = '" . $conexion->real_escape_string($zona_filtro) . "'";
if ($buscar)      $where[] = "v.nombre LIKE '%" . $conexion->real_escape_string($buscar) . "%'";
$where_sql = implode(" AND ", $where);

$vacantes_q = $conexion->query("
    SELECT v.id, v.nombre, v.ubicacion, v.lat, v.lng, e.nombre as empresa
    FROM vacantes v
    LEFT JOIN empresas e ON e.id = v.empresa_id
    WHERE $where_sql
    ORDER BY v.ubicacion ASC, v.nombre ASC
");

// Construir array de vacantes y geocodificar las que no tienen coords
$vacantes_arr = [];
while ($v = $vacantes_q->fetch_assoc()) {
    if (!empty($v['ubicacion']) && (empty($v['lat']) || empty($v['lng']))) {
        $coords = geocodificar($v['ubicacion'], $conexion, $v['id']);
        if ($coords) {
            $v['lat'] = $coords['lat'];
            $v['lng'] = $coords['lng'];
        }
        // Pequeña pausa para no saturar Nominatim
        usleep(300000);
    }
    $vacantes_arr[] = $v;
}

$total_vacantes = count($vacantes_arr);

// Vacantes con coordenadas para el mapa
$vacantes_mapa = array_filter($vacantes_arr, fn($v) => !empty($v['lat']) && !empty($v['lng']));
?>

<!-- HERO -->
<section style="background:#0f2544;padding:80px 0 50px;">
    <div class="container text-center">
        <span class="section-label">Oportunidades laborales</span>
        <h1 style="color:#fff;font-weight:800;font-size:2.4rem;margin-top:10px;">
            Vacantes <span style="color:#66FCF1;">disponibles</span>
        </h1>
        <p style="color:rgba(255,255,255,.6);margin-top:12px;font-size:1rem;">
            Encuentra tu próxima oportunidad. Contáctanos para más información.
        </p>
        <form method="GET" style="display:flex;gap:10px;max-width:500px;margin:28px auto 0;justify-content:center;">
            <input type="text" name="buscar" value="<?= htmlspecialchars($buscar) ?>"
                   placeholder="Buscar puesto..."
                   style="flex:1;border-radius:50px;border:none;padding:12px 20px;font-size:.92rem;outline:none;">
            <?php if ($zona_filtro): ?>
            <input type="hidden" name="zona" value="<?= htmlspecialchars($zona_filtro) ?>">
            <?php endif; ?>
            <button type="submit" style="background:#66FCF1;color:#0f2544;border:none;border-radius:50px;padding:12px 24px;font-weight:700;cursor:pointer;">
                <i class="bi bi-search"></i>
            </button>
        </form>
    </div>
</section>

<!-- MAPA -->
<?php if (!empty($vacantes_mapa)): ?>
<section style="background:#f4f7fb;padding:40px 0 0;">
    <div class="container">
        <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
                <h5 style="font-weight:800;color:#0f2544;margin:0;">Ubicaciones</h5>
                <p style="color:#8896a7;font-size:.85rem;margin:2px 0 0;">
                    <?= count($vacantes_mapa) ?> vacante<?= count($vacantes_mapa) !== 1 ? 's' : '' ?> en el mapa
                </p>
            </div>
        </div>
        <div id="mapa-vacantes"></div>
    </div>
</section>
<?php endif; ?>

<!-- CONTENIDO -->
<section style="background:#f4f7fb;padding:40px 0 80px;">
    <div class="container">
        <div class="row g-4">

            <!-- Sidebar zonas -->
            <div class="col-lg-3">
                <div style="background:#fff;border-radius:14px;padding:20px;box-shadow:0 2px 12px rgba(0,0,0,.06);position:sticky;top:20px;">
                    <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:14px;">
                        Filtrar por zona
                    </p>
                    <a href="vacantes.php<?= $buscar ? '?buscar='.urlencode($buscar) : '' ?>"
                       style="display:flex;align-items:center;padding:9px 12px;border-radius:9px;text-decoration:none;font-size:.88rem;font-weight:600;margin-bottom:4px;
                       background:<?= !$zona_filtro ? '#0f2544' : 'transparent' ?>;
                       color:<?= !$zona_filtro ? '#66FCF1' : '#0f2544' ?>;">
                        <i class="bi bi-grid me-2"></i>Todas las zonas
                    </a>
                    <?php foreach ($zonas as $zona): ?>
                    <a href="vacantes.php?zona=<?= urlencode($zona) ?><?= $buscar ? '&buscar='.urlencode($buscar) : '' ?>"
                       style="display:flex;align-items:center;padding:9px 12px;border-radius:9px;text-decoration:none;font-size:.88rem;font-weight:500;margin-bottom:4px;
                       background:<?= $zona_filtro === $zona ? 'rgba(15,37,68,.07)' : 'transparent' ?>;
                       color:<?= $zona_filtro === $zona ? '#0f2544' : '#4a5568' ?>;
                       border-left:<?= $zona_filtro === $zona ? '3px solid #66FCF1' : '3px solid transparent' ?>;">
                        <i class="bi bi-geo-alt me-2" style="color:#66FCF1;flex-shrink:0;"></i>
                        <?= htmlspecialchars($zona) ?>
                    </a>
                    <?php endforeach; ?>

                    <div style="margin-top:24px;padding-top:20px;border-top:1px solid #e2e8f0;">
                        <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:10px;">¿Te interesa?</p>
                        <a href="index.php#contacto"
                           style="display:flex;align-items:center;justify-content:center;gap:8px;background:#0f2544;color:#66FCF1;border-radius:9px;padding:10px;font-weight:700;font-size:.85rem;text-decoration:none;">
                            <i class="bi bi-envelope"></i> Contáctanos
                        </a>
                    </div>
                </div>
            </div>

            <!-- Lista vacantes -->
            <div class="col-lg-9">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
                    <div>
                        <h5 style="font-weight:800;color:#0f2544;margin:0;">
                            <?= $zona_filtro ? htmlspecialchars($zona_filtro) : 'Todas las vacantes' ?>
                        </h5>
                        <p style="color:#8896a7;font-size:.86rem;margin:2px 0 0;">
                            <?= $total_vacantes ?> puesto<?= $total_vacantes !== 1 ? 's' : '' ?> disponible<?= $total_vacantes !== 1 ? 's' : '' ?>
                            <?= $buscar ? ' para "' . htmlspecialchars($buscar) . '"' : '' ?>
                        </p>
                    </div>
                    <?php if ($zona_filtro || $buscar): ?>
                    <a href="vacantes.php" style="font-size:.84rem;color:#8896a7;text-decoration:none;display:flex;align-items:center;gap:4px;">
                        <i class="bi bi-x-circle"></i> Limpiar filtros
                    </a>
                    <?php endif; ?>
                </div>

                <?php if ($total_vacantes > 0):
                    $zona_actual = null;
                    foreach ($vacantes_arr as $v):
                        if (!$zona_filtro && $v['ubicacion'] !== $zona_actual):
                            $zona_actual = $v['ubicacion'];
                ?>
                <div style="display:flex;align-items:center;gap:10px;margin:<?= $zona_actual ? '28px' : '0' ?> 0 14px;">
                    <i class="bi bi-geo-alt-fill" style="color:#66FCF1;font-size:1rem;"></i>
                    <span style="font-size:.78rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#0f2544;">
                        <?= htmlspecialchars($v['ubicacion'] ?: 'Sin ubicación') ?>
                    </span>
                    <div style="flex:1;height:1px;background:#e2e8f0;"></div>
                </div>
                <?php endif; ?>

                <!-- Tarjeta vacante -->
                <div style="background:#fff;border-radius:12px;padding:18px 22px;margin-bottom:10px;box-shadow:0 2px 8px rgba(0,0,0,.05);display:flex;align-items:center;justify-content:space-between;gap:16px;transition:box-shadow .2s,transform .2s;"
                     onmouseover="this.style.boxShadow='0 6px 24px rgba(0,0,0,.1)';this.style.transform='translateY(-2px)'"
                     onmouseout="this.style.boxShadow='0 2px 8px rgba(0,0,0,.05)';this.style.transform=''">

                    <div style="display:flex;align-items:center;gap:14px;flex:1;min-width:0;">
                        <div style="width:44px;height:44px;background:rgba(15,37,68,.07);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="bi bi-briefcase" style="color:#0f2544;font-size:1.1rem;"></i>
                        </div>
                        <div style="overflow:hidden;">
                            <div style="font-weight:700;font-size:.96rem;color:#0f2544;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= htmlspecialchars($v['nombre']) ?>
                            </div>
                            <div style="font-size:.8rem;color:#8896a7;margin-top:3px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                <span style="display:flex;align-items:center;gap:4px;">
                                    <i class="bi bi-geo-alt" style="color:#66FCF1;"></i>
                                    <?= htmlspecialchars($v['ubicacion'] ?: 'Ubicación no especificada') ?>
                                </span>
                                <?php if (!empty($v['lat']) && !empty($v['lng'])): ?>
                                <a href="https://www.google.com/maps?q=<?= $v['lat'] ?>,<?= $v['lng'] ?>" target="_blank"
                                   style="display:inline-flex;align-items:center;gap:3px;font-size:.76rem;color:#0f2544;font-weight:600;text-decoration:none;background:rgba(15,37,68,.07);padding:2px 8px;border-radius:50px;">
                                    <i class="bi bi-map" style="color:#66FCF1;"></i> Ver en mapa
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <a href="index.php#contacto"
                       style="display:inline-flex;align-items:center;gap:6px;background:#0f2544;color:#66FCF1;border-radius:9px;padding:9px 18px;font-weight:700;font-size:.82rem;text-decoration:none;white-space:nowrap;flex-shrink:0;transition:background .2s;"
                       onmouseover="this.style.background='#1a3a6e'" onmouseout="this.style.background='#0f2544'">
                        <i class="bi bi-send"></i> Me interesa
                    </a>
                </div>

                <?php endforeach;
                else: ?>
                <div style="background:#fff;border-radius:14px;padding:60px 20px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.05);">
                    <i class="bi bi-briefcase" style="font-size:3rem;color:#e2e8f0;"></i>
                    <p style="color:#8896a7;margin-top:16px;font-size:.95rem;">
                        <?= $buscar || $zona_filtro ? 'No hay vacantes con esos filtros.' : 'No hay vacantes disponibles en este momento.' ?>
                    </p>
                    <?php if ($buscar || $zona_filtro): ?>
                    <a href="vacantes.php" style="color:#0f2544;font-size:.88rem;font-weight:600;">Ver todas las vacantes</a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<!-- FOOTER -->
<footer class="site-footer">
    <p>© <?php echo date('Y'); ?> <span>Nexus RH Soluciones</span>. Todos los derechos reservados.</p>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<!-- Nav toggle móvil -->
<script>
(function () {
    const toggle = document.getElementById('navToggle');
    const nav    = document.getElementById('mainNav');
    if (!toggle || !nav) return;
    toggle.addEventListener('click', function () {
        const open = nav.classList.toggle('open');
        toggle.querySelector('i').className = open ? 'bi bi-x' : 'bi bi-list';
    });
    nav.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            nav.classList.remove('open');
            toggle.querySelector('i').className = 'bi bi-list';
        });
    });
})();
</script>

<!-- Mapa Leaflet -->
<?php if (!empty($vacantes_mapa)): ?>
<script>
(function () {
    const vacantes = <?= json_encode(array_values($vacantes_mapa)) ?>;

    // Centro del mapa: promedio de coordenadas
    const lats = vacantes.map(v => parseFloat(v.lat));
    const lngs = vacantes.map(v => parseFloat(v.lng));
    const centerLat = lats.reduce((a,b) => a+b, 0) / lats.length;
    const centerLng = lngs.reduce((a,b) => a+b, 0) / lngs.length;

    const map = L.map('mapa-vacantes').setView([centerLat, centerLng], 11);

    // Tiles OpenStreetMap
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 18
    }).addTo(map);

    // Ícono personalizado
    const iconoVacante = L.divIcon({
        className: '',
        html: `<div style="
            width:36px;height:36px;
            background:#0f2544;
            border:3px solid #66FCF1;
            border-radius:50% 50% 50% 0;
            transform:rotate(-45deg);
            display:flex;align-items:center;justify-content:center;
            box-shadow:0 2px 8px rgba(0,0,0,0.3);
        "><div style="transform:rotate(45deg);color:#66FCF1;font-size:14px;">💼</div></div>`,
        iconSize: [36, 36],
        iconAnchor: [18, 36],
        popupAnchor: [0, -38]
    });

    // Agregar marcadores
    vacantes.forEach(function (v) {
        const lat = parseFloat(v.lat);
        const lng = parseFloat(v.lng);
        if (!lat || !lng) return;

        const popup = `
            <div class="popup-vacante" style="min-width:180px;">
                <h6>${v.nombre}</h6>
                <p><i class="bi bi-geo-alt" style="color:#66FCF1;"></i> ${v.ubicacion || ''}</p>
                <span class="badge-zona">${v.empresa || ''}</span><br>
                <a href="https://www.google.com/maps?q=${lat},${lng}" target="_blank"
                   style="display:inline-flex;align-items:center;gap:4px;margin-top:8px;font-size:.78rem;font-weight:700;color:#0f2544;text-decoration:none;background:rgba(15,37,68,.08);padding:4px 10px;border-radius:50px;">
                    <span>📍</span> Abrir en Google Maps
                </a>
            </div>`;

        L.marker([lat, lng], { icon: iconoVacante })
         .addTo(map)
         .bindPopup(popup);
    });

    // Ajustar zoom para mostrar todos los pins
    if (vacantes.length > 1) {
        const bounds = L.latLngBounds(vacantes.map(v => [parseFloat(v.lat), parseFloat(v.lng)]));
        map.fitBounds(bounds, { padding: [40, 40] });
    }
})();
</script>
<?php endif; ?>

</body>
</html>