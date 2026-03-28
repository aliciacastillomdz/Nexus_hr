-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-03-2026 a las 22:45:00
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `nexus_rh`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contacto_mensajes`
--

CREATE TABLE `contacto_mensajes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `empresa` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `asunto` varchar(200) DEFAULT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `contacto_mensajes`
--

INSERT INTO `contacto_mensajes` (`id`, `nombre`, `empresa`, `telefono`, `email`, `asunto`, `mensaje`, `leido`, `created_at`) VALUES
(1, 'Tomas', 'LA que sea', '8281009368', 'tomas_alex11@hotmail.com', 'Urgente Trabajos etc prueba', 'Quiero poner a prueba el sistema de mensajes.', 1, '2026-03-27 21:12:32'),
(2, 'Tomás', 'LA que sea', '2123414212', 'admin@nexusrh.com', 'Urgente Trabajos etc prueba', 'prueba', 1, '2026-03-27 21:13:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresas`
--

CREATE TABLE `empresas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `instrucciones` text DEFAULT NULL,
  `formulario_url` varchar(500) DEFAULT NULL,
  `perfiles_url` varchar(500) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresas`
--

INSERT INTO `empresas` (`id`, `nombre`, `descripcion`, `instrucciones`, `formulario_url`, `perfiles_url`, `activo`, `created_at`) VALUES
(1, 'Gemsa', NULL, NULL, NULL, '', 1, '2026-03-06 20:24:31'),
(2, 'Grupo Emmont de México - GEMSA', NULL, NULL, NULL, 'https://docs.google.com/forms/d/e/1FAIpQLSeOVTeDX9eP0D2JZuezfbQiZ514Eg2JOYvmAC1FNEXISrAfCg/viewform', 1, '2026-03-06 21:22:10'),
(3, 'Empresa Ejemplo S.A.', NULL, NULL, NULL, '', 1, '2026-03-13 22:14:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empresa_links`
--

CREATE TABLE `empresa_links` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `url` varchar(500) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `empresa_links`
--

INSERT INTO `empresa_links` (`id`, `empresa_id`, `titulo`, `url`, `created_at`) VALUES
(1, 1, 'De la Unidad hasta Santa Martha', 'https://www.facebook.com/groups/605361963222593/', '2026-03-06 21:25:57'),
(2, 1, 'SANTA MARTHA / LA 18 DE OCTUBRE / LA UNIDAD / VALLE DE SAN MIGUEL / VNTS', 'https://www.facebook.com/groups/883792116130235/', '2026-03-06 21:26:21'),
(3, 2, 'SANTA MARTHA / LA 18 DE OCTUBRE / LA UNIDAD / VALLE DE SAN MIGUEL / VNTS', 'https://www.facebook.com/groups/883792116130235/', '2026-03-06 21:26:31');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipo_nexus`
--

CREATE TABLE `equipo_nexus` (
  `id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `puesto` varchar(150) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `publicaciones`
--

CREATE TABLE `publicaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `link_id` int(11) DEFAULT NULL,
  `link` varchar(500) NOT NULL,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuestas_rapidas`
--

CREATE TABLE `respuestas_rapidas` (
  `id` int(11) NOT NULL,
  `vacante_id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `contenido` text NOT NULL,
  `orden` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitantes`
--

CREATE TABLE `solicitantes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `empresa_id` int(11) DEFAULT NULL,
  `vacante_id` int(11) DEFAULT NULL,
  `nombre` varchar(150) NOT NULL,
  `edad` int(3) DEFAULT NULL,
  `colonia` varchar(150) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `estudios` enum('Primaria','Secundaria','Preparatoria','Técnico','Licenciatura','Posgrado','Otro') DEFAULT NULL,
  `experiencia` text DEFAULT NULL,
  `fecha_cita` date DEFAULT NULL,
  `infonavit_fonacot` tinyint(1) DEFAULT 0,
  `descuento_monto` varchar(100) DEFAULT NULL,
  `adeudo_santander` tinyint(1) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `estatus` enum('Nuevo contacto','Entrevista agendada','Entrevista realizada','Contratado','No continuó') DEFAULT 'Nuevo contacto',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `solicitantes`
--

INSERT INTO `solicitantes` (`id`, `usuario_id`, `empresa_id`, `vacante_id`, `nombre`, `edad`, `colonia`, `telefono`, `estudios`, `experiencia`, `fecha_cita`, `infonavit_fonacot`, `descuento_monto`, `adeudo_santander`, `observaciones`, `estatus`, `created_at`) VALUES
(3, 1, 1, NULL, 'Tomas', 22, 'dadad', '2123414212', 'Preparatoria', 'algo', '2026-03-09', 1, '15%', 1, 'Irresponsable', 'Contratado', '2026-03-06 20:29:44'),
(4, 1, 1, 2, 'Tomas', 22, 'Las palmas', '8281009368', 'Técnico', 'Escuelas', '2026-03-07', 0, '', 0, 'Responsable', 'No continuó', '2026-03-06 23:32:02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','usuario') DEFAULT 'usuario',
  `puesto` varchar(150) DEFAULT NULL,
  `telefono` varchar(30) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `rol`, `puesto`, `telefono`, `fecha_ingreso`, `notas`, `created_at`) VALUES
(1, 'Tomás12', 'admin@nexusrh.com', '$2y$10$QsmVXJ8SxfmwqUl0/dUQ3Ok8ntmHkDE3igXOE2WTM3XO7kJ0FLgX.', 'admin', 'Prácticante prueba', '', NULL, '', '2026-03-06 20:20:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacantes`
--

CREATE TABLE `vacantes` (
  `id` int(11) NOT NULL,
  `empresa_id` int(11) NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `requisitos` text DEFAULT NULL,
  `sueldo` varchar(150) DEFAULT NULL,
  `horario` varchar(150) DEFAULT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vacantes`
--

INSERT INTO `vacantes` (`id`, `empresa_id`, `nombre`, `descripcion`, `requisitos`, `sueldo`, `horario`, `ubicacion`, `lat`, `lng`, `activo`, `created_at`) VALUES
(1, 1, 'Ayudante General', 'SI', 'SI', 'Hasta: $2,787 semanal  Esto incluye 👀 - Sueldo semanal  $2,222.60 - Premio de asistencia semanal $175 - Premio de puntualidad semanal $175 - Vales de', 'Lunes a domingo, descanso rotativo. 1) ⏰ 6:00 am - 2:00 pm  2)⏰  2:00 pm - 10:00 pm 3) ⏰ 10:00 pm- 6:00 am', '0', NULL, NULL, 1, '2026-03-06 20:24:52'),
(2, 1, 'Ayudante General 2', 'Lo mismo', 'dadadw', 'Hasta: $2,787 semanal  Esto incluye 👀 - Sueldo semanal  $2,222.60 - Premio de asistencia semanal $175 - Premio de puntualidad semanal $175 - Vales de', 'Lunes a domingo, descanso rotativo. 1) ⏰ 6:00 am - 2:00 pm  2)⏰  2:00 pm - 10:00 pm 3) ⏰ 10:00 pm- 6:00 am', 'Av. Camino a las pedreras, Escobedo NL', NULL, NULL, 1, '2026-03-06 20:25:23'),
(4, 3, 'Ejemplo Mapa', 'Ejemplo para el mapa', 'Ejemplos', 'Ejem', 'plo', 'Av. Camino a las pedreras, Escobedo NL', NULL, NULL, 1, '2026-03-20 19:00:37'),
(5, 3, 'Ejemplo Mapa', 'Ejemplo', 'Ejemplos', 'Ejem', 'plo', 'Ciudad General Escobedo Nuevo León', 25.8437813, -100.3946114, 1, '2026-03-20 20:12:05');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `contacto_mensajes`
--
ALTER TABLE `contacto_mensajes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empresas`
--
ALTER TABLE `empresas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `empresa_links`
--
ALTER TABLE `empresa_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- Indices de la tabla `equipo_nexus`
--
ALTER TABLE `equipo_nexus`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `link_id` (`link_id`);

--
-- Indices de la tabla `respuestas_rapidas`
--
ALTER TABLE `respuestas_rapidas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vacante_id` (`vacante_id`);

--
-- Indices de la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `empresa_id` (`empresa_id`),
  ADD KEY `vacante_id` (`vacante_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `empresa_id` (`empresa_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contacto_mensajes`
--
ALTER TABLE `contacto_mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `empresas`
--
ALTER TABLE `empresas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `empresa_links`
--
ALTER TABLE `empresa_links`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `equipo_nexus`
--
ALTER TABLE `equipo_nexus`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `respuestas_rapidas`
--
ALTER TABLE `respuestas_rapidas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `vacantes`
--
ALTER TABLE `vacantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empresa_links`
--
ALTER TABLE `empresa_links`
  ADD CONSTRAINT `empresa_links_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `publicaciones`
--
ALTER TABLE `publicaciones`
  ADD CONSTRAINT `publicaciones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `publicaciones_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `publicaciones_ibfk_3` FOREIGN KEY (`link_id`) REFERENCES `empresa_links` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `respuestas_rapidas`
--
ALTER TABLE `respuestas_rapidas`
  ADD CONSTRAINT `respuestas_rapidas_ibfk_1` FOREIGN KEY (`vacante_id`) REFERENCES `vacantes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitantes`
--
ALTER TABLE `solicitantes`
  ADD CONSTRAINT `solicitantes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `solicitantes_ibfk_2` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `solicitantes_ibfk_3` FOREIGN KEY (`vacante_id`) REFERENCES `vacantes` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `vacantes`
--
ALTER TABLE `vacantes`
  ADD CONSTRAINT `vacantes_ibfk_1` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
