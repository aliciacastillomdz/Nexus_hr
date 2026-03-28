<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexus RH Soluciones</title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
</head>
<body>

<!-- HEADER -->
<header class="header">
    <div class="logo">Nexus RH</div>

    <!-- Botón hamburguesa (solo visible en móvil vía CSS) -->
    <button class="nav-toggle" id="navToggle" aria-label="Menú">
        <i class="bi bi-list"></i>
    </button>

    <nav id="mainNav">
        <a href="#inicio">Inicio</a>
        <a href="#nosotros">Nosotros</a>
        <a href="#servicios">Servicios</a>
        <a href="#contacto">Contacto</a>
        <a href="vacantes.php" class="btn-vacantes">Vacantes</a>
        <a href="login.php" class="btn-login">Ingresar</a>
    </nav>
</header>

<!-- HERO -->
<section id="inicio" class="hero p-0">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-inner">

            <div class="carousel-item active">
                <div class="carousel-bg" style="background-image:url('img/rrhh.png');"></div>
                <div class="hero-content">
                    <h1>Conectamos el <span>Talento</span><br>con el Futuro</h1>
                    <p>Soluciones estratégicas de Recursos Humanos para organizaciones que buscan crecer con las personas correctas.</p>
                    <a href="#servicios" class="hero-btn">Conoce nuestros servicios</a>
                </div>
            </div>

            <div class="carousel-item">
                <div class="carousel-bg" style="background-image:url('img/rrhh.png');"></div>
                <div class="hero-content">
                    <h1>Selección <span>profesional</span><br>y estratégica</h1>
                    <p>Evaluamos perfiles con procesos claros, eficientes y orientados a resultados reales.</p>
                    <a href="#contacto" class="hero-btn">Contáctanos</a>
                </div>
            </div>

        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
</section>

<!-- NOSOTROS -->
<section class="bloque" id="nosotros" style="background:#f4f7fb;">
    <div class="container">
        <div class="row align-items-center g-5">

            <div class="col-lg-6">
                <span class="section-label">Nuestra Historia</span>
                <h2 class="section-title">Más de una década<br>construyendo equipos</h2>
                <p style="color:#8896a7;line-height:1.8;margin-bottom:14px;">
                    Nexus RH Soluciones nació con el objetivo de brindar servicios de reclutamiento y selección de personal con enfoque humano, profesional y estratégico.
                </p>
                <p style="color:#8896a7;line-height:1.8;">
                    Hemos acompañado a organizaciones en la construcción de equipos sólidos, confiables y alineados a sus objetivos de negocio.
                </p>
            </div>

            <div class="col-lg-6">
                <div class="row g-3">
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-value" style="font-size:2.4rem;">+500</div>
                            <div class="stat-label mt-2">Candidatos colocados</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-value" style="font-size:2.4rem;">+80</div>
                            <div class="stat-label mt-2">Empresas atendidas</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-value" style="font-size:2.4rem;">10+</div>
                            <div class="stat-label mt-2">Años de experiencia</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="stat-card text-center">
                            <div class="stat-value" style="font-size:2.4rem;">98%</div>
                            <div class="stat-label mt-2">Satisfacción del cliente</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- SERVICIOS -->
<section class="bloque bg-white" id="servicios">
    <div class="container text-center">
        <span class="section-label">Lo que hacemos</span>
        <h2 class="section-title">Nuestros Servicios</h2>
        <p class="section-sub mb-5">Soluciones integrales diseñadas para cubrir cada etapa del ciclo de gestión del talento humano.</p>

        <div class="row gy-4">
            <div class="col-md-4">
                <div class="service-card text-start">
                    <div class="service-icon"><i class="bi bi-search"></i></div>
                    <h5>Reclutamiento</h5>
                    <p>Identificamos y atraemos talento calificado acorde a las necesidades específicas de tu organización.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card text-start">
                    <div class="service-icon"><i class="bi bi-people"></i></div>
                    <h5>Selección de Personal</h5>
                    <p>Evaluamos perfiles con procesos claros y eficientes para encontrar al candidato ideal.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card text-start">
                    <div class="service-icon"><i class="bi bi-lightbulb"></i></div>
                    <h5>Asesoría RH</h5>
                    <p>Apoyamos la toma de decisiones estratégicas relacionadas con el capital humano.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card text-start">
                    <div class="service-icon"><i class="bi bi-graph-up"></i></div>
                    <h5>Evaluación de Competencias</h5>
                    <p>Medimos habilidades técnicas y blandas para garantizar ajuste con el perfil requerido.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card text-start">
                    <div class="service-icon"><i class="bi bi-briefcase"></i></div>
                    <h5>Bolsa de Trabajo</h5>
                    <p>Conectamos candidatos con oportunidades laborales en empresas líderes de la región.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="service-card text-start">
                    <div class="service-icon"><i class="bi bi-shield-check"></i></div>
                    <h5>Verificación de Referencias</h5>
                    <p>Validamos antecedentes laborales para una contratación segura y confiable.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
require_once("sistema/conexion.php");

$msg_contacto  = "";
$msg_tipo      = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_contacto'])) {
    $nombre   = $conexion->real_escape_string(trim($_POST['nombre']   ?? ''));
    $empresa  = $conexion->real_escape_string(trim($_POST['empresa']  ?? ''));
    $telefono = $conexion->real_escape_string(trim($_POST['telefono'] ?? ''));
    $email    = $conexion->real_escape_string(trim($_POST['email']    ?? ''));
    $asunto   = $conexion->real_escape_string(trim($_POST['asunto']   ?? ''));
    $mensaje  = $conexion->real_escape_string(trim($_POST['mensaje']  ?? ''));

    if (empty($nombre) || empty($mensaje)) {
        $msg_contacto = "Por favor completa al menos tu nombre y mensaje.";
        $msg_tipo     = "danger";
    } else {
        $conexion->query("INSERT INTO contacto_mensajes (nombre, empresa, telefono, email, asunto, mensaje)
            VALUES ('$nombre','$empresa','$telefono','$email','$asunto','$mensaje')");

        if ($conexion->affected_rows > 0) {
            $msg_contacto = "¡Mensaje enviado! Nos pondremos en contacto contigo pronto.";
            $msg_tipo     = "success";
        } else {
            $msg_contacto = "Ocurrió un error. Por favor intenta de nuevo.";
            $msg_tipo     = "danger";
        }
    }
}
?>

<!-- CONTACTO -->
<section id="contacto" class="contact-section">
    <div class="container">

        <div class="text-center text-white mb-5">
            <span class="section-label">Contáctanos</span>
            <h2 class="section-title text-white">¿Cómo podemos ayudarte?</h2>
            <p style="color:rgba(255,255,255,0.6);">Estamos listos para brindarte la mejor solución según tus necesidades.</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="contact-card">
                    <div class="row g-0">

                        <div class="col-md-5 contact-info">
                            <div>
                                <h5>Ponte en contacto</h5>
                                <div class="info-item">
                                    <strong>Oficina</strong>
                                    <span>Naranjo 205, Jardines de Cadereyta, Cadereyta Jiménez, NL, C.P. 67480</span>
                                </div>
                                <div class="info-item">
                                    <strong>Email</strong>
                                    <span>diana.nexusrhsoluciones@outlook.com</span>
                                </div>
                                <div class="info-item">
                                    <strong>Teléfono</strong>
                                    <span>+52 81 11 75 08 55</span>
                                </div>
                            </div>
                            <div class="social mt-4">
                                <a href="#"><i class="bi bi-facebook"></i></a>
                                <a href="#"><i class="bi bi-instagram"></i></a>
                                <a href="#"><i class="bi bi-linkedin"></i></a>
                            </div>
                        </div>

                        <div class="col-md-7 contact-form-area">
                            <h5>Envíanos un mensaje</h5>

                            <?php if (!empty($msg_contacto)): ?>
                            <div class="alert alert-<?= $msg_tipo ?> d-flex align-items-center gap-2 mb-3"
                                 style="border-radius:9px;font-size:.88rem;">
                                <i class="bi <?= $msg_tipo === 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?>"></i>
                                <?= htmlspecialchars($msg_contacto) ?>
                            </div>
                            <?php endif; ?>

                            <form method="POST" action="#contacto">
                                <input type="hidden" name="accion_contacto" value="1">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="nombre" class="form-control"
                                               placeholder="Nombre *" required
                                               value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="empresa" class="form-control"
                                               placeholder="Empresa"
                                               value="<?= htmlspecialchars($_POST['empresa'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <input type="text" name="telefono" class="form-control"
                                               placeholder="Teléfono"
                                               value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <input type="email" name="email" class="form-control"
                                               placeholder="Email"
                                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <input type="text" name="asunto" class="form-control"
                                           placeholder="Asunto"
                                           value="<?= htmlspecialchars($_POST['asunto'] ?? '') ?>">
                                </div>
                                <div class="mb-3">
                                    <textarea name="mensaje" class="form-control" rows="4"
                                              placeholder="Mensaje *" required><?= htmlspecialchars($_POST['mensaje'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" class="btn-send">Enviar mensaje</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
<footer class="site-footer">
    <p>© <?php echo date('Y'); ?> <span>Nexus RH Soluciones</span>. Todos los derechos reservados.</p>
</footer>

<script src="js/bootstrap.bundle.min.js"></script>

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
    // Cerrar al hacer clic en un link del nav
    nav.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
            nav.classList.remove('open');
            toggle.querySelector('i').className = 'bi bi-list';
        });
    });
})();
</script>

</body>
</html>