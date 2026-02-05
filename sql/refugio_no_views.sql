-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 24-11-2025 a las 09:09:01
-- Versión del servidor: 12.0.2-MariaDB
-- Versión de PHP: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `refugio`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acompanantes`
--

CREATE TABLE `acompanantes` (
  `id` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `num_socio` varchar(20) DEFAULT NULL,
  `es_socio` tinyint(1) DEFAULT 0,
  `dni` varchar(9) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido1` varchar(50) NOT NULL,
  `apellido2` varchar(50) DEFAULT NULL,
  `actividad` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `camas`
--

CREATE TABLE `camas` (
  `id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `id_habitacion` int(11) NOT NULL,
  `estado` enum('libre','pendiente','reservada') NOT NULL DEFAULT 'libre'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `camas`
--

INSERT INTO `camas` (`id`, `numero`, `id_habitacion`, `estado`) VALUES
(1, 1, 1, 'reservada'),
(2, 2, 1, 'reservada'),
(3, 3, 1, 'reservada'),
(4, 4, 1, 'reservada'),
(5, 1, 2, 'reservada'),
(6, 2, 2, 'reservada'),
(7, 3, 2, 'reservada'),
(8, 4, 2, 'reservada'),
(9, 1, 3, 'reservada'),
(10, 2, 3, 'reservada'),
(11, 3, 3, 'reservada'),
(12, 4, 3, 'reservada'),
(13, 1, 4, 'reservada'),
(14, 2, 4, 'reservada'),
(15, 3, 4, 'reservada'),
(16, 4, 4, 'reservada'),
(17, 5, 4, 'reservada'),
(18, 6, 4, 'reservada'),
(19, 7, 4, 'reservada'),
(20, 8, 4, 'reservada'),
(21, 9, 4, 'reservada'),
(22, 10, 4, 'reservada'),
(23, 11, 4, 'reservada'),
(24, 12, 4, 'reservada'),
(25, 13, 4, 'reservada'),
(26, 14, 4, 'reservada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habitaciones`
--

CREATE TABLE `habitaciones` (
  `id` int(11) NOT NULL,
  `numero` int(11) NOT NULL,
  `capacidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `habitaciones`
--

INSERT INTO `habitaciones` (`id`, `numero`, `capacidad`) VALUES
(1, 1, 4),
(2, 2, 4),
(3, 3, 4),
(4, 4, 14);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL COMMENT 'NULL para reservas especiales del admin',
  `id_habitacion` int(11) DEFAULT NULL,
  `numero_camas` tinyint(4) DEFAULT NULL,
  `id_cama` int(11) DEFAULT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `estado` enum('pendiente','reservada','cancelada') NOT NULL DEFAULT 'pendiente',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `observaciones` varchar(500) DEFAULT NULL COMMENT 'Motivo/evento para reservas especiales'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `id_usuario`, `id_habitacion`, `numero_camas`, `id_cama`, `fecha_inicio`, `fecha_fin`, `estado`, `fecha_creacion`, `observaciones`) VALUES
(1, 2, 2, 1, 5, '2025-10-25', '2025-10-28', 'reservada', '2025-10-23 11:04:11', NULL),
(2, NULL, 1, 4, NULL, '2025-10-27', '2025-10-31', 'cancelada', '2025-10-23 12:36:36', 'TODO EL REFUGIO - test'),
(3, NULL, 2, 3, NULL, '2025-10-27', '2025-10-31', 'cancelada', '2025-10-23 12:36:36', 'TODO EL REFUGIO - test'),
(4, NULL, 3, 4, NULL, '2025-10-27', '2025-10-31', 'cancelada', '2025-10-23 12:36:36', 'TODO EL REFUGIO - test'),
(5, NULL, 4, 14, NULL, '2025-10-27', '2025-10-31', 'cancelada', '2025-10-23 12:36:36', 'TODO EL REFUGIO - test'),
(6, NULL, NULL, 26, NULL, '2025-11-08', '2025-11-15', 'reservada', '2025-10-23 13:07:03', 'TODO EL REFUGIO - testNew');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas_camas`
--

CREATE TABLE `reservas_camas` (
  `id` int(11) NOT NULL,
  `id_reserva` int(11) NOT NULL,
  `id_cama` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas_camas`
--

INSERT INTO `reservas_camas` (`id`, `id_reserva`, `id_cama`) VALUES
(1, 1, 5),
(2, 2, 1),
(3, 2, 2),
(4, 2, 3),
(5, 2, 4),
(6, 3, 6),
(7, 3, 7),
(8, 3, 8),
(9, 4, 9),
(10, 4, 10),
(11, 4, 11),
(12, 4, 12),
(13, 5, 13),
(14, 5, 14),
(15, 5, 15),
(16, 5, 16),
(17, 5, 17),
(18, 5, 18),
(19, 5, 19),
(20, 5, 20),
(21, 5, 21),
(22, 5, 22),
(23, 5, 23),
(24, 5, 24),
(25, 5, 25),
(26, 5, 26),
(27, 6, 1),
(28, 6, 2),
(29, 6, 3),
(30, 6, 4),
(52, 6, 5),
(31, 6, 6),
(32, 6, 7),
(33, 6, 8),
(34, 6, 9),
(35, 6, 10),
(36, 6, 11),
(37, 6, 12),
(38, 6, 13),
(39, 6, 14),
(40, 6, 15),
(41, 6, 16),
(42, 6, 17),
(43, 6, 18),
(44, 6, 19),
(45, 6, 20),
(46, 6, 21),
(47, 6, 22),
(48, 6, 23),
(49, 6, 24),
(50, 6, 25),
(51, 6, 26);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `num_socio` varchar(20) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `telf` varchar(15) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido1` varchar(50) NOT NULL,
  `apellido2` varchar(50) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `rol` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `num_socio`, `dni`, `telf`, `email`, `nombre`, `apellido1`, `apellido2`, `password`, `foto_perfil`, `rol`) VALUES
(1, 'A001', '00000000A', '600000000', 'admin@hostel.com', 'Admin', 'General', 'System', '$2y$12$txTtkpHhMn23dmfotTgiS.S6esy7C37EyYf/g.HKODk8GuUlJvGu.', NULL, 'admin'),
(2, 'U001', '12345678B', '611111111', 'user1@mail.com', 'Carlos', 'Pérez', 'Gómez', '$2y$12$Y8/XKy8fRpfd.7vPwtmdZ.6SrtR.KJbonuBn3HruA.AiIO998DZJy', 'uploads/perfiles/perfil_2_1761221607.jpg', 'user'),
(3, 'U002', '23456789C', '622222222', 'user2@mail.com', 'Lucía', 'López', 'Martín', '$2y$12$Y8/XKy8fRpfd.7vPwtmdZ.6SrtR.KJbonuBn3HruA.AiIO998DZJy', NULL, 'user');

-- --------------------------------------------------------

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acompanantes`
--
ALTER TABLE `acompanantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reserva` (`id_reserva`);

--
-- Indices de la tabla `camas`
--
ALTER TABLE `camas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cama_unica_por_habitacion` (`numero`,`id_habitacion`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_habitacion` (`id_habitacion`);

--
-- Indices de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero` (`numero`),
  ADD KEY `idx_numero` (`numero`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reserva_unica_cama_periodo` (`id_cama`,`fecha_inicio`,`fecha_fin`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_usuario` (`id_usuario`),
  ADD KEY `idx_fechas` (`fecha_inicio`,`fecha_fin`),
  ADD KEY `idx_habitacion` (`id_habitacion`);

--
-- Indices de la tabla `reservas_camas`
--
ALTER TABLE `reservas_camas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reserva_cama_unica` (`id_reserva`,`id_cama`),
  ADD KEY `idx_reserva` (`id_reserva`),
  ADD KEY `idx_cama` (`id_cama`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `num_socio` (`num_socio`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_rol` (`rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acompanantes`
--
ALTER TABLE `acompanantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `camas`
--
ALTER TABLE `camas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `habitaciones`
--
ALTER TABLE `habitaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `reservas_camas`
--
ALTER TABLE `reservas_camas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `acompanantes`
--
ALTER TABLE `acompanantes`
  ADD CONSTRAINT `fk_acompanante_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `camas`
--
ALTER TABLE `camas`
  ADD CONSTRAINT `fk_cama_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_reserva_cama` FOREIGN KEY (`id_cama`) REFERENCES `camas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reserva_habitacion` FOREIGN KEY (`id_habitacion`) REFERENCES `habitaciones` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_reserva_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas_camas`
--
ALTER TABLE `reservas_camas`
  ADD CONSTRAINT `fk_rc_cama` FOREIGN KEY (`id_cama`) REFERENCES `camas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_rc_reserva` FOREIGN KEY (`id_reserva`) REFERENCES `reservas` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
