-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 19-05-2021 a las 22:18:31
-- Versión del servidor: 10.1.19-MariaDB
-- Versión de PHP: 5.6.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `localiz3_database`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `contactados`
--

CREATE TABLE `contactados` (
  `id` int(23) NOT NULL,
  `solicitante` varchar(100) NOT NULL,
  `solicitado` varchar(100) NOT NULL,
  `texto_solicitante` text NOT NULL,
  `pendiente` tinyint(4) NOT NULL,
  `aceptado` tinyint(4) NOT NULL,
  `created_At` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `contactados`
--

INSERT INTO `contactados` (`id`, `solicitante`, `solicitado`, `texto_solicitante`, `pendiente`, `aceptado`, `created_At`) VALUES
(67, 'pepe@pepe.com', 'clara@clara', 'hola soy pepe ', 0, 1, '2017-01-10 20:38:17'),
(80, 'popo', 'pepe@pepe.com', 'jdhdhs', 0, 1, '2017-06-15 22:50:09'),
(92, 'popo', 'aa@a.com', 'hola soy popo', 0, 1, '2021-04-15 19:50:31'),
(93, 'a@a.com', 'aa@a.com', 'soy a ', 0, 1, '2021-04-15 19:51:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `coordenadas`
--

CREATE TABLE `coordenadas` (
  `unique_id` varchar(23) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `sesion_num` int(10) UNSIGNED NOT NULL,
  `point` int(10) UNSIGNED NOT NULL,
  `latitud` double NOT NULL,
  `longitud` double NOT NULL,
  `altitud` double NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int(10) UNSIGNED NOT NULL,
  `unique_id` varchar(23) CHARACTER SET latin1 COLLATE latin1_bin NOT NULL,
  `session_num` int(10) UNSIGNED NOT NULL,
  `live` tinyint(4) NOT NULL,
  `time_paused` int(10) UNSIGNED NOT NULL,
  `creado_el` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `unique_id` varchar(23) NOT NULL,
  `name` varchar(25) NOT NULL,
  `email` varchar(40) NOT NULL,
  `islogged` tinyint(4) NOT NULL,
  `isloggedweb` tinyint(4) NOT NULL,
  `Token` varchar(200) DEFAULT NULL,
  `inBackground` tinyint(4) NOT NULL,
  `encrypted_password` varchar(80) NOT NULL,
  `salt` varchar(10) NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `unique_id`, `name`, `email`, `islogged`, `isloggedweb`, `Token`, `inBackground`, `encrypted_password`, `salt`, `created_at`, `updated_at`) VALUES
(1, '5686c1f5d38da5.60904479', 'pepe', 'pepe@pepe.com', 0, 0, NULL, 0, 'rZzzFqRIpdeTmM9aIEGN3CZSkUEzZDhhMjEzYTYw', '3d8a213a60', '2016-01-01 19:14:13', NULL),
(23, '569edadde04149.31578771', ' clara', 'clara@clara', 0, 0, NULL, 0, 'rNsYvx7VPkEX93TrGIiPpBlhZQZjZmEyMjFjNGNl', 'cfa221c4ce', '2016-01-20 01:54:53', NULL),
(40, '593f2d3fbdeae2.92190329', ' popo', 'popo', 0, 0, NULL, 0, 'pwu4ci0xmBlDKhxKrRC7O4C2snVjMTljMGMyYWI4', 'c19c0c2ab8', '2017-06-12 20:09:35', NULL),
(47, '5a95a2ce36e611.88572237', ' a', 'a@a.com', 0, 0, 'NULL', 0, 'mmDjeS8MZ9HqJvIbsKKseVnbKmo2YWU2OGE4MWFi', '6ae68a81ab', '2018-02-27 13:26:22', NULL),
(53, '5b551bc468b572.13392926', ' aa', 'aa@a.com', 0, 0, 'NULL', 0, 'Mjur4l4gzhnr6JGVO7kvSFbrZWFkZWFjNDMxZTY1', 'deac431e65', '2018-07-22 20:05:24', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `contactados`
--
ALTER TABLE `contactados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `coordenadas`
--
ALTER TABLE `coordenadas`
  ADD PRIMARY KEY (`point`),
  ADD KEY `sesion_numb` (`point`),
  ADD KEY `sesion_num` (`sesion_num`),
  ADD KEY `Cliente_id` (`unique_id`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `creado_el` (`creado_el`),
  ADD KEY `cliente_id` (`unique_id`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `contactados`
--
ALTER TABLE `contactados`
  MODIFY `id` int(23) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=94;
--
-- AUTO_INCREMENT de la tabla `coordenadas`
--
ALTER TABLE `coordenadas`
  MODIFY `point` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15857;
--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1284;
--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
