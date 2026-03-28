<?php
require_once("../sistema/conexion.php");

if ($_SESSION['rol'] !== 'admin') {
    header("Location: index.php?page=perfiles");
    exit();
}

$msg      = "";
$msg_tipo = "";
$empresa_id = isset($_GET['empresa']) ? (int)$_GET['empresa'] : 0;

// ── Función geocodificación ──────────────────────────────────
function geocodificar_ubicacion($ubicacion) {
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
    return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon']];
}

// ── Acciones POST ────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // Crear vacante
    if ($accion === 'crear_vacante') {
        $eid         = (int)$_POST['empresa_id'];
        $nombre      = $conexion->real_escape_string(trim($_POST['nombre']));
        $descripcion = $conexion->real_escape_string(trim($_POST['descripcion']));
        $requisitos  = $conexion->real_escape_string(trim($_POST['requisitos']));
        $sueldo      = $conexion->real_escape_string(trim($_POST['sueldo']));
        $horario     = $conexion->real_escape_string(trim($_POST['horario']));
        $ubicacion   = $conexion->real_escape_string(trim($_POST['ubicacion']));
        $lat         = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
        $lng         = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
        $lat_sql     = $lat ? $lat : 'NULL';
        $lng_sql     = $lng ? $lng : 'NULL';

        $conexion->query("INSERT INTO vacantes (empresa_id, nombre, descripcion, requisitos, sueldo, horario, ubicacion, lat, lng)
            VALUES ($eid, '$nombre', '$descripcion', '$requisitos', '$sueldo', '$horario', '$ubicacion', $lat_sql, $lng_sql)");

        $msg      = "Vacante creada." . ($lat ? " Ubicación en el mapa guardada ✓" : " Sin coordenadas — coloca un pin en el mapa.");
        $msg_tipo = "success";
    }

    // Editar vacante
    if ($accion === 'editar_vacante') {
        $vid         = (int)$_POST['vacante_id'];
        $nombre      = $conexion->real_escape_string(trim($_POST['nombre']));
        $descripcion = $conexion->real_escape_string(trim($_POST['descripcion']));
        $requisitos  = $conexion->real_escape_string(trim($_POST['requisitos']));
        $sueldo      = $conexion->real_escape_string(trim($_POST['sueldo']));
        $horario     = $conexion->real_escape_string(trim($_POST['horario']));
        $ubicacion   = $conexion->real_escape_string(trim($_POST['ubicacion']));
        $activo      = isset($_POST['activo']) ? 1 : 0;
        $lat         = !empty($_POST['lat']) ? (float)$_POST['lat'] : null;
        $lng         = !empty($_POST['lng']) ? (float)$_POST['lng'] : null;
        $lat_parte   = $lat ? ", lat=$lat, lng=$lng" : "";

        $conexion->query("UPDATE vacantes SET
            nombre='$nombre', descripcion='$descripcion', requisitos='$requisitos',
            sueldo='$sueldo', horario='$horario', ubicacion='$ubicacion', activo=$activo
            $lat_parte WHERE id=$vid");

        $msg      = "Vacante actualizada." . ($lat ? " Coordenadas guardadas ✓" : "");
        $msg_tipo = "success";
    }

    // Eliminar vacante
    if ($accion === 'eliminar_vacante') {
        $vid = (int)$_POST['vacante_id'];
        $conexion->query("DELETE FROM vacantes WHERE id = $vid");
        $msg = "Vacante eliminada."; $msg_tipo = "success";
    }

    // Agregar respuesta rápida
    if ($accion === 'agregar_respuesta') {
        $vid       = (int)$_POST['vacante_id'];
        $titulo    = trim($_POST['titulo']);
        $contenido = trim($_POST['contenido']);
        $orden     = (int)($_POST['orden'] ?? 0);
        if (!empty($titulo) && !empty($contenido)) {
            $stmt = $conexion->prepare("INSERT INTO respuestas_rapidas (vacante_id, titulo, contenido, orden) VALUES (?,?,?,?)");
            $stmt->bind_param("issi", $vid, $titulo, $contenido, $orden);
            $stmt->execute();
            $msg = "Respuesta agregada."; $msg_tipo = "success";
            $stmt->close();
        }
    }

    // Eliminar respuesta
    if ($accion === 'eliminar_respuesta') {
        $rid = (int)$_POST['respuesta_id'];
        $conexion->query("DELETE FROM respuestas_rapidas WHERE id = $rid");
        $msg = "Respuesta eliminada."; $msg_tipo = "success";
    }

    // Mantener empresa en vista
    if (isset($_POST['empresa_id'])) $empresa_id = (int)$_POST['empresa_id'];
    if (isset($_POST['emp_id']))     $empresa_id = (int)$_POST['emp_id'];
}

// ── Cargar empresa seleccionada o lista ──────────────────────
$empresas = $conexion->query("SELECT * FROM empresas WHERE activo = 1 ORDER BY nombre ASC");
$emp = $empresa_id > 0 ? $conexion->query("SELECT * FROM empresas WHERE id = $empresa_id")->fetch_assoc() : null;
?>

<!-- Encabezado -->
<div class="d-flex align-items-center gap-3 mb-4">
    <?php if ($emp): ?>
    <a href="index.php?page=gestionar_vacantes"
       style="display:inline-flex;align-items:center;justify-content:center;width:36px;height:36px;background:#f4f7fb;border-radius:9px;border:1.5px solid #e2e8f0;text-decoration:none;color:#0f2544;flex-shrink:0;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <?php endif; ?>
    <div style="flex:1;">
        <h4 style="font-weight:800;color:#0f2544;margin:0;">
            <?= $emp ? 'Vacantes — ' . htmlspecialchars($emp['nombre']) : 'Gestionar Vacantes' ?>
        </h4>
        <p style="color:#8896a7;margin:2px 0 0;font-size:.86rem;">
            <?= $emp ? 'Administra vacantes y respuestas rápidas.' : 'Selecciona una empresa para gestionar sus vacantes.' ?>
        </p>
    </div>
    <?php if ($emp): ?>
    <a href="index.php?page=perfiles&empresa=<?= $empresa_id ?>"
       style="background:#f4f7fb;color:#0f2544;border-radius:50px;padding:8px 20px;font-weight:600;font-size:.85rem;text-decoration:none;border:1.5px solid #e2e8f0;flex-shrink:0;">
        <i class="bi bi-eye me-1"></i>Ver como usuario
    </a>
    <?php endif; ?>
</div>

<?php if (!empty($msg)): ?>
<div class="alert alert-<?= $msg_tipo ?> d-flex align-items-center gap-2 mb-4" style="border-radius:10px;font-size:.88rem;">
    <i class="bi <?= $msg_tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
    <?= htmlspecialchars($msg) ?>
</div>
<?php endif; ?>

<!-- ── Seleccionar empresa si no hay ninguna ─────────────────── -->
<?php if (!$emp): ?>
<div class="row g-3">
    <?php while ($e = $empresas->fetch_assoc()): ?>
    <div class="col-md-6 col-lg-4">
        <a href="index.php?page=gestionar_vacantes&empresa=<?= $e['id'] ?>" style="text-decoration:none;">
            <div class="panel-card" style="transition:transform .2s,box-shadow .2s;cursor:pointer;"
                 onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 30px rgba(0,0,0,.1)'"
                 onmouseout="this.style.transform='';this.style.boxShadow=''">
                <div style="background:#0f2544;padding:16px 20px;">
                    <h6 style="color:#66FCF1;font-weight:700;margin:0;"><i class="bi bi-building me-2"></i><?= htmlspecialchars($e['nombre']) ?></h6>
                </div>
                <div style="padding:14px 20px;font-size:.85rem;color:#0f2544;font-weight:600;">
                    Gestionar vacantes <i class="bi bi-arrow-right ms-1"></i>
                </div>
            </div>
        </a>
    </div>
    <?php endwhile; ?>
</div>

<?php else: ?>

<!-- ── Formulario nueva vacante ─────────────────────────────── -->
<div class="panel-card mb-4">
    <div class="panel-card-header">
        <h6><i class="bi bi-plus-circle me-2" style="color:#66FCF1;"></i>Nueva Vacante</h6>
    </div>
    <div style="padding:24px;">
        <form method="POST">
            <input type="hidden" name="accion"     value="crear_vacante">
            <input type="hidden" name="empresa_id" value="<?= $empresa_id ?>">
            <div class="row g-3">
                <div class="col-md-6">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Nombre del puesto *</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej. Ayudante General" required
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-3">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Sueldo</label>
                    <input type="text" name="sueldo" class="form-control" placeholder="Ej. $200/día"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-3">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Horario</label>
                    <input type="text" name="horario" class="form-control" placeholder="Ej. Lun-Vie 7am-5pm"
                           style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                </div>
                <div class="col-md-6">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Descripción general</label>
                    <textarea name="descripcion" class="form-control" rows="3" placeholder="Describe el puesto..."
                              style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;resize:vertical;"></textarea>
                </div>
                <div class="col-md-6">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Requisitos</label>
                    <textarea name="requisitos" class="form-control" rows="3" placeholder="Lista los requisitos del puesto..."
                              style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;resize:vertical;"></textarea>
                </div>
                <div class="col-12">
                    <label style="font-size:.78rem;font-weight:700;color:#0f2544;display:block;margin-bottom:5px;">Ubicación</label>
                    <div style="display:flex;gap:8px;margin-bottom:8px;">
                        <input type="text" id="buscar-crear" class="form-control" placeholder="Busca la dirección y presiona Enter..."
                               style="border-radius:9px;font-size:.88rem;border:1.5px solid #e2e8f0;">
                        <button type="button" onclick="buscarDireccion('crear')"
                                style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:9px 16px;font-weight:600;font-size:.85rem;cursor:pointer;white-space:nowrap;">
                            <i class="bi bi-search"></i> Buscar
                        </button>
                    </div>
                    <div id="mapa-crear" style="height:280px;border-radius:10px;border:1.5px solid #e2e8f0;margin-bottom:8px;"></div>
                    <div style="display:flex;gap:8px;">
                        <input type="text" name="ubicacion" id="ubicacion-crear" class="form-control" placeholder="Nombre de la ubicación (editable)"
                               style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                        <input type="hidden" name="lat" id="lat-crear">
                        <input type="hidden" name="lng" id="lng-crear">
                    </div>
                    <p style="font-size:.74rem;color:#8896a7;margin:5px 0 0;">
                        <i class="bi bi-info-circle me-1"></i>Haz clic en el mapa para colocar el pin o busca la dirección arriba.
                    </p>
                </div>
            </div>
            <div style="margin-top:16px;">
                <button type="submit"
                        style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:10px 24px;font-weight:700;font-size:.88rem;cursor:pointer;">
                    <i class="bi bi-plus me-1"></i>Crear vacante
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── Vacantes existentes ──────────────────────────────────── -->
<?php
$vacantes = $conexion->query("SELECT * FROM vacantes WHERE empresa_id = $empresa_id ORDER BY id ASC");
if ($vacantes && $vacantes->num_rows > 0):
    while ($v = $vacantes->fetch_assoc()):
        $vid        = (int)$v['id'];
        $respuestas = $conexion->query("SELECT * FROM respuestas_rapidas WHERE vacante_id = $vid ORDER BY orden ASC, id ASC");
?>

<div class="panel-card mb-4">
    <div class="panel-card-header" style="background:#0f2544;">
        <h6 style="color:#66FCF1;margin:0;font-weight:700;">
            <i class="bi bi-briefcase me-2"></i><?= htmlspecialchars($v['nombre']) ?>
        </h6>
        <span style="font-size:.75rem;padding:3px 10px;border-radius:50px;font-weight:600;
              background:<?= $v['activo'] ? 'rgba(102,252,241,.15)' : 'rgba(255,100,100,.15)' ?>;
              color:<?= $v['activo'] ? '#66FCF1' : '#ff8080' ?>;">
            <?= $v['activo'] ? 'Activa' : 'Inactiva' ?>
        </span>
    </div>

    <div style="padding:24px;">
        <div class="row g-4">

            <!-- Editar vacante -->
            <div class="col-lg-5">
                <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:12px;">Editar vacante</p>
                <form method="POST">
                    <input type="hidden" name="accion"     value="editar_vacante">
                    <input type="hidden" name="vacante_id" value="<?= $vid ?>">
                    <input type="hidden" name="emp_id"     value="<?= $empresa_id ?>">
                    <div class="mb-2">
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($v['nombre']) ?>" required
                               style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <input type="text" name="sueldo" class="form-control" value="<?= htmlspecialchars($v['sueldo']) ?>" placeholder="Sueldo"
                                   style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                        </div>
                        <div class="col-6">
                            <input type="text" name="horario" class="form-control" value="<?= htmlspecialchars($v['horario']) ?>" placeholder="Horario"
                                   style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                        </div>
                    </div>
                    <div class="mb-2">
                        <div style="display:flex;gap:6px;margin-bottom:6px;">
                            <input type="text" id="buscar-<?= $vid ?>" class="form-control" placeholder="Buscar dirección..."
                                   style="border-radius:9px;font-size:.84rem;border:1.5px solid #e2e8f0;">
                            <button type="button" onclick="buscarDireccion('<?= $vid ?>')"
                                    style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:7px 12px;font-size:.82rem;cursor:pointer;white-space:nowrap;">
                                <i class="bi bi-search"></i>
                            </button>
                        </div>
                        <div id="mapa-<?= $vid ?>" style="height:220px;border-radius:10px;border:1.5px solid #e2e8f0;margin-bottom:6px;"></div>
                        <input type="text" name="ubicacion" id="ubicacion-<?= $vid ?>" class="form-control"
                               value="<?= htmlspecialchars($v['ubicacion']) ?>" placeholder="Ubicación"
                               style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                        <input type="hidden" name="lat" id="lat-<?= $vid ?>" value="<?= $v['lat'] ?>">
                        <input type="hidden" name="lng" id="lng-<?= $vid ?>" value="<?= $v['lng'] ?>">
                        <p style="font-size:.72rem;color:#8896a7;margin:4px 0 0;">
                            <i class="bi bi-info-circle me-1"></i>Haz clic en el mapa para mover el pin.
                            <?php if (!empty($v['lat'])): ?>
                            <span style="color:#0f6b62;">✓ Coordenadas guardadas</span>
                            <?php else: ?>
                            <span style="color:#e67e22;">Sin coordenadas — coloca el pin en el mapa</span>
                            <?php endif; ?>
                        </p>
                    </div>
                    <div class="mb-2">
                        <textarea name="descripcion" class="form-control" rows="3" placeholder="Descripción"
                                  style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;resize:vertical;"><?= htmlspecialchars($v['descripcion']) ?></textarea>
                    </div>
                    <div class="mb-3">
                        <textarea name="requisitos" class="form-control" rows="3" placeholder="Requisitos"
                                  style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;resize:vertical;"><?= htmlspecialchars($v['requisitos']) ?></textarea>
                    </div>
                    <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;">
                        <label style="font-size:.83rem;color:#0f2544;display:flex;align-items:center;gap:6px;cursor:pointer;">
                            <input type="checkbox" name="activo" <?= $v['activo'] ? 'checked' : '' ?>> Activa
                        </label>
                        <button type="submit"
                                style="background:#0f2544;color:#66FCF1;border:none;border-radius:9px;padding:8px 18px;font-weight:700;font-size:.83rem;cursor:pointer;">
                            <i class="bi bi-check2 me-1"></i>Guardar
                        </button>
                    </div>
                </form>

                <form method="POST" style="margin-top:10px;" onsubmit="return confirm('¿Eliminar esta vacante?')">
                    <input type="hidden" name="accion"     value="eliminar_vacante">
                    <input type="hidden" name="vacante_id" value="<?= $vid ?>">
                    <input type="hidden" name="emp_id"     value="<?= $empresa_id ?>">
                    <button type="submit"
                            style="background:rgba(220,53,69,.1);color:#dc3545;border:1px solid rgba(220,53,69,.2);border-radius:9px;padding:7px 16px;font-weight:600;font-size:.82rem;cursor:pointer;width:100%;">
                        <i class="bi bi-trash me-1"></i>Eliminar vacante
                    </button>
                </form>
            </div>

            <!-- Respuestas rápidas -->
            <div class="col-lg-7">
                <p style="font-size:.72rem;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:#8896a7;margin-bottom:12px;">Respuestas Rápidas</p>

                <!-- Lista existentes -->
                <?php if ($respuestas && $respuestas->num_rows > 0): ?>
                <div style="display:flex;flex-direction:column;gap:8px;margin-bottom:14px;">
                    <?php while ($r = $respuestas->fetch_assoc()): ?>
                    <div style="background:#f4f7fb;border-radius:9px;padding:10px 12px;border:1px solid #e2e8f0;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:4px;">
                            <span style="font-size:.82rem;font-weight:700;color:#0f2544;"><?= htmlspecialchars($r['titulo']) ?></span>
                            <form method="POST" style="margin:0;" onsubmit="return confirm('¿Eliminar?')">
                                <input type="hidden" name="accion"      value="eliminar_respuesta">
                                <input type="hidden" name="respuesta_id" value="<?= $r['id'] ?>">
                                <input type="hidden" name="emp_id"      value="<?= $empresa_id ?>">
                                <button type="submit" style="background:none;border:none;color:#dc3545;cursor:pointer;font-size:.85rem;padding:0 4px;">
                                    <i class="bi bi-x-circle"></i>
                                </button>
                            </form>
                        </div>
                        <p style="font-size:.82rem;color:#8896a7;margin:0;line-height:1.5;white-space:pre-line;"><?= htmlspecialchars(mb_strimwidth($r['contenido'], 0, 100, '...')) ?></p>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php endif; ?>

                <!-- Agregar respuesta -->
                <form method="POST">
                    <input type="hidden" name="accion"     value="agregar_respuesta">
                    <input type="hidden" name="vacante_id" value="<?= $vid ?>">
                    <input type="hidden" name="emp_id"     value="<?= $empresa_id ?>">
                    <div class="mb-2">
                        <input type="text" name="titulo" class="form-control" placeholder="Título (Ej. Respuesta inicial)"
                               style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;">
                    </div>
                    <div class="mb-2">
                        <textarea name="contenido" class="form-control" rows="4"
                                  placeholder="Escribe el texto de la respuesta rápida..."
                                  style="border-radius:9px;font-size:.86rem;border:1.5px solid #e2e8f0;resize:vertical;"></textarea>
                    </div>
                    <button type="submit"
                            style="background:rgba(15,37,68,.08);color:#0f2544;border:1.5px solid #e2e8f0;border-radius:9px;padding:8px 18px;font-weight:700;font-size:.83rem;cursor:pointer;">
                        <i class="bi bi-plus me-1"></i>Agregar respuesta
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
        <i class="bi bi-briefcase" style="font-size:3rem;opacity:.25;"></i>
        <p class="mt-3 mb-0">Aún no hay vacantes. Crea la primera arriba.</p>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- Leaflet CSS + JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Centro por defecto: Monterrey, NL
var DEFAULT_LAT = 25.6866;
var DEFAULT_LNG = -100.3161;
var mapas = {};

function iniciarMapa(id, lat, lng) {
    var centerLat = (lat && lat != 0) ? parseFloat(lat) : DEFAULT_LAT;
    var centerLng = (lng && lng != 0) ? parseFloat(lng) : DEFAULT_LNG;
    var zoom      = (lat && lat != 0) ? 15 : 11;

    var map = L.map('mapa-' + id).setView([centerLat, centerLng], zoom);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19
    }).addTo(map);

    var marker = null;

    // Si ya tiene coordenadas, poner el pin
    if (lat && lat != 0) {
        marker = L.marker([centerLat, centerLng], { draggable: true }).addTo(map);
        marker.on('dragend', function () {
            var pos = marker.getLatLng();
            document.getElementById('lat-' + id).value = pos.lat.toFixed(7);
            document.getElementById('lng-' + id).value = pos.lng.toFixed(7);
        });
    }

    // Clic en el mapa
    map.on('click', function (e) {
        var lat = e.latlng.lat;
        var lng = e.latlng.lng;

        if (marker) {
            marker.setLatLng([lat, lng]);
        } else {
            marker = L.marker([lat, lng], { draggable: true }).addTo(map);
            marker.on('dragend', function () {
                var pos = marker.getLatLng();
                document.getElementById('lat-' + id).value = pos.lat.toFixed(7);
                document.getElementById('lng-' + id).value = pos.lng.toFixed(7);
            });
        }

        document.getElementById('lat-' + id).value = lat.toFixed(7);
        document.getElementById('lng-' + id).value = lng.toFixed(7);

        // Reverse geocoding para nombre
        fetch('https://nominatim.openstreetmap.org/reverse?lat=' + lat + '&lon=' + lng + '&format=json', {
            headers: { 'User-Agent': 'NexusRH/1.0' }
        })
        .then(r => r.json())
        .then(data => {
            if (data && data.display_name) {
                var campo = document.getElementById('ubicacion-' + id);
                if (campo && campo.value === '') {
                    campo.value = data.display_name.split(',').slice(0,3).join(',').trim();
                }
            }
        })
        .catch(() => {});
    });

    mapas[id] = { map: map, marker: function() { return marker; }, setMarker: function(m) { marker = m; } };
}

function buscarDireccion(id) {
    var input = document.getElementById('buscar-' + id);
    if (!input || !input.value.trim()) return;

    var query = encodeURIComponent(input.value + ', México');
    fetch('https://nominatim.openstreetmap.org/search?q=' + query + '&format=json&limit=1', {
        headers: { 'User-Agent': 'NexusRH/1.0' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data || data.length === 0) {
            alert('No se encontró la dirección. Intenta con "Ciudad, Estado".');
            return;
        }
        var lat = parseFloat(data[0].lat);
        var lng = parseFloat(data[0].lon);
        var m   = mapas[id];
        if (!m) return;

        m.map.setView([lat, lng], 16);
        m.map.fire('click', { latlng: L.latLng(lat, lng) });

        // Actualizar campo ubicación con el resultado
        var campo = document.getElementById('ubicacion-' + id);
        if (campo) campo.value = input.value;
    })
    .catch(() => alert('Error al buscar. Verifica tu conexión.'));
}

// Iniciar mapa del formulario crear
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('mapa-crear')) {
        iniciarMapa('crear', null, null);
    }

    // Iniciar mapas de edición
    <?php
    // Re-query vacantes para obtener lat/lng
    if (isset($empresa_id) && $empresa_id > 0) {
        $vacs_js = $conexion->query("SELECT id, lat, lng FROM vacantes WHERE empresa_id = $empresa_id ORDER BY id ASC");
        if ($vacs_js) {
            while ($vj = $vacs_js->fetch_assoc()) {
                $jlat = $vj['lat'] ? $vj['lat'] : 'null';
                $jlng = $vj['lng'] ? $vj['lng'] : 'null';
                echo "    if (document.getElementById('mapa-{$vj['id']}')) { iniciarMapa('{$vj['id']}', $jlat, $jlng); }\n";
            }
        }
    }
    ?>
});
</script>