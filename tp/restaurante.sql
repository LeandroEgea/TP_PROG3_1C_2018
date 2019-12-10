-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-12-2019 a las 04:19:42
-- Versión del servidor: 10.4.6-MariaDB
-- Versión de PHP: 7.3.8

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `restaurante`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encargados`
--

CREATE TABLE `encargados` (
  `id` int(11) NOT NULL,
  `nombre` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `apellido` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `usuario` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `idRol` int(11) NOT NULL,
  `clave` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `encargados`
--

INSERT INTO `encargados` (`id`, `nombre`, `apellido`, `usuario`, `idRol`, `clave`, `updated_at`, `created_at`) VALUES
(1, 'Leandro', 'Egea', 'leandroe', 3, 'pass', '2019-11-13 18:41:26', '2019-11-09 19:16:41'),
(35, 'Cocinero', 'Gomez', 'cocinerog', 1, 'pass', '2019-12-10 06:44:00', '2019-12-10 06:42:40'),
(36, 'Cervecero', 'Gomez', 'cervecerog', 2, 'pass', '2019-12-10 06:46:11', '2019-12-10 06:42:55'),
(37, 'Bartender', 'Gomez', 'bartenderg', 4, 'pass', '2019-12-10 06:46:18', '2019-12-10 06:43:12'),
(38, 'Mozo', 'Gomez', 'mozog', 5, 'pass', '2019-12-10 06:43:28', '2019-12-10 06:43:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_mesas`
--

CREATE TABLE `estados_mesas` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `estados_mesas`
--

INSERT INTO `estados_mesas` (`id`, `descripcion`, `created_at`, `updated_at`) VALUES
(1, 'ocupada', '2019-12-08 16:41:27', '2019-12-08 16:41:27'),
(2, 'comiendo', '2019-12-08 16:41:27', '2019-12-08 16:41:27'),
(3, 'pagando', '2019-12-08 16:41:27', '2019-12-08 16:41:27'),
(4, 'libre', '2019-12-08 16:41:27', '2019-12-08 16:41:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_pedidos`
--

CREATE TABLE `estados_pedidos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `estados_pedidos`
--

INSERT INTO `estados_pedidos` (`id`, `descripcion`, `updated_at`, `created_at`) VALUES
(1, 'pendiente', '2019-11-09 19:29:48', '2019-11-09 19:29:48'),
(2, 'en preparacion', '2019-11-09 19:30:16', '2019-11-09 19:30:16'),
(3, 'listo para servir', '2019-12-08 18:34:54', '2019-12-08 18:34:54'),
(4, 'servido', '2019-12-08 20:29:34', '2019-12-08 20:29:34'),
(5, 'cobrado', '2019-12-08 21:28:33', '2019-12-08 21:28:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_productos`
--

CREATE TABLE `estados_productos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `estados_productos`
--

INSERT INTO `estados_productos` (`id`, `descripcion`, `updated_at`, `created_at`) VALUES
(1, 'pendiente', '2019-11-09 19:24:39', '2019-11-09 19:24:39'),
(2, 'en preparacion', '2019-11-09 19:24:55', '2019-11-09 19:24:55'),
(3, 'listo para servir', '2019-11-09 19:25:23', '2019-11-09 19:25:23'),
(4, 'servido', '2019-12-08 20:31:23', '2019-12-08 20:31:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `codigoMesa` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `idEstadoMesa` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `codigoMesa`, `idEstadoMesa`, `updated_at`, `created_at`) VALUES
(1, 'MSA01', 4, '2019-12-10 07:11:48', '2019-11-16 00:01:34'),
(2, 'MSA02', 4, '2019-12-09 07:05:35', '2019-11-16 00:01:34'),
(3, 'MSA03', 4, '2019-12-09 07:00:05', '2019-11-16 00:01:34'),
(5, 'MSA05', 4, '2019-12-09 07:14:59', '2019-12-02 09:23:54'),
(7, 'MSA07', 4, '2019-12-10 06:50:10', '2019-12-10 06:50:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `idEstadoPedido` int(11) NOT NULL,
  `codigoPedido` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `codigoMesa` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `idEncargado` int(11) NOT NULL,
  `nombreCliente` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `imagen` varchar(1024) COLLATE utf8_spanish2_ci NOT NULL,
  `tiempo` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `idEstadoPedido`, `codigoPedido`, `codigoMesa`, `idEncargado`, `nombreCliente`, `imagen`, `tiempo`, `updated_at`, `created_at`) VALUES
(3, 5, '8RjdK', 'MSA05', 1, 'Rodolfo', 'C:\\xampp\\tmp\\php9121.tmp', 0, '2019-12-09 04:01:55', '2019-12-09 02:54:58'),
(4, 5, 'RXgSI', 'MSA05', 1, 'Rodolfo', 'C:\\xampp\\tmp\\php31E8.tmp', 0, '2019-12-09 06:14:08', '2019-12-09 05:51:30'),
(10, 5, '60S4z', 'MSA01', 38, 'Rodolfo', './images/clients/60S4z.jpg', 0, '2019-12-10 07:11:48', '2019-12-10 07:02:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_productos`
--

CREATE TABLE `pedidos_productos` (
  `id` int(11) NOT NULL,
  `idPedido` int(11) NOT NULL,
  `idProducto` int(11) NOT NULL,
  `idEstadoProducto` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `pedidos_productos`
--

INSERT INTO `pedidos_productos` (`id`, `idPedido`, `idProducto`, `idEstadoProducto`, `updated_at`, `created_at`) VALUES
(1, 3, 3, 4, '2019-12-09 03:29:04', '2019-12-09 02:54:58'),
(2, 3, 5, 4, '2019-12-09 03:29:04', '2019-12-09 02:54:58'),
(3, 3, 5, 4, '2019-12-09 03:29:04', '2019-12-09 02:54:58'),
(4, 3, 6, 4, '2019-12-09 03:29:04', '2019-12-09 02:54:58'),
(5, 4, 3, 4, '2019-12-09 05:53:46', '2019-12-09 05:51:30'),
(6, 4, 5, 4, '2019-12-09 05:53:46', '2019-12-09 05:51:30'),
(7, 4, 5, 4, '2019-12-09 05:53:46', '2019-12-09 05:51:30'),
(8, 4, 8, 4, '2019-12-09 05:53:46', '2019-12-09 05:51:30'),
(29, 10, 1, 4, '2019-12-10 07:10:02', '2019-12-10 07:02:15'),
(30, 10, 7, 4, '2019-12-10 07:10:03', '2019-12-10 07:02:15'),
(31, 10, 7, 4, '2019-12-10 07:10:03', '2019-12-10 07:02:15'),
(32, 10, 8, 4, '2019-12-10 07:10:03', '2019-12-10 07:02:15'),
(33, 10, 14, 4, '2019-12-10 07:10:03', '2019-12-10 07:02:15'),
(34, 10, 15, 4, '2019-12-10 07:10:03', '2019-12-10 07:02:15'),
(35, 10, 7, 4, '2019-12-10 07:10:03', '2019-12-10 07:03:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `precio` int(11) NOT NULL,
  `idRol` int(11) NOT NULL,
  `tiempoPreparacion` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `descripcion`, `precio`, `idRol`, `tiempoPreparacion`, `updated_at`, `created_at`) VALUES
(1, 'Coca-Cola', 80, 5, 3, '2019-12-02 11:20:56', '2019-12-02 11:20:56'),
(2, 'Fanta', 90, 5, 3, '2019-12-02 11:21:12', '2019-12-02 11:21:12'),
(3, 'Paso de los Toros', 75, 5, 3, '2019-12-02 11:21:38', '2019-12-02 11:21:38'),
(4, 'Cafe', 90, 5, 5, '2019-12-02 11:21:51', '2019-12-02 11:21:51'),
(5, 'Agua Sin Gas', 60, 5, 3, '2019-12-02 11:22:59', '2019-12-02 11:22:59'),
(6, 'Agua Con Gas', 70, 5, 3, '2019-12-02 11:23:19', '2019-12-02 11:23:19'),
(7, 'Milanesa con Fritas', 300, 1, 15, '2019-12-02 11:23:45', '2019-12-02 11:23:45'),
(8, 'Tallarines', 250, 1, 16, '2019-12-02 11:27:56', '2019-12-02 11:27:56'),
(10, 'Pollo', 240, 1, 15, '2019-12-02 11:25:32', '2019-12-02 11:25:32'),
(11, 'Vino tinto', 300, 4, 6, '2019-11-12 19:43:05', '2019-11-12 15:43:05'),
(12, 'Racion de Papas Fritas', 150, 1, 15, '2019-12-02 11:28:07', '2019-12-02 11:28:07'),
(14, 'Cuba Libre', 200, 4, 3, '2019-12-02 11:26:31', '2019-12-02 11:26:31'),
(15, 'Cerveza', 150, 2, 6, '2019-11-12 19:43:51', '2019-11-12 15:43:51'),
(18, 'Asado para Dos\r\n', 500, 1, 12, '2019-12-02 11:27:12', '2019-12-02 11:27:12'),
(20, 'Mirinda', 99, 5, 2, '2019-12-05 06:53:35', '2019-12-05 06:53:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `cargo` varchar(64) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `cargo`, `updated_at`, `created_at`) VALUES
(1, 'cocinero', '2019-11-09 19:50:01', '2019-11-09 19:50:01'),
(2, 'cervecero', '2019-11-09 19:50:17', '2019-11-09 19:50:17'),
(3, 'socio', '2019-11-09 19:50:22', '2019-11-09 19:50:22'),
(4, 'bartender', '2019-11-09 19:50:46', '2019-11-09 19:50:46'),
(5, 'mozo', '2019-11-09 19:50:54', '2019-11-09 19:50:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id` int(11) NOT NULL,
  `idPedido` int(11) NOT NULL,
  `precioTotal` int(11) NOT NULL,
  `pagado` tinyint(4) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `tickets`
--

INSERT INTO `tickets` (`id`, `idPedido`, `precioTotal`, `pagado`, `created_at`, `updated_at`) VALUES
(1, 3, 265, 1, '2019-12-09 03:53:34', '2019-12-09 04:01:55'),
(4, 4, 445, 1, '2019-12-09 06:04:32', '2019-12-09 06:14:08'),
(7, 10, 1580, 1, '2019-12-10 07:10:55', '2019-12-10 07:11:48');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `encargados`
--
ALTER TABLE `encargados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD KEY `idRol` (`idRol`);

--
-- Indices de la tabla `estados_mesas`
--
ALTER TABLE `estados_mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indices de la tabla `estados_pedidos`
--
ALTER TABLE `estados_pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indices de la tabla `estados_productos`
--
ALTER TABLE `estados_productos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigoMesa` (`codigoMesa`),
  ADD KEY `idEstadoMesa` (`idEstadoMesa`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigoPedido` (`codigoPedido`),
  ADD KEY `idEstadoPedido` (`idEstadoPedido`),
  ADD KEY `codigoMesa` (`codigoMesa`),
  ADD KEY `idEncargado` (`idEncargado`);

--
-- Indices de la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idPedido` (`idPedido`),
  ADD KEY `idProducto` (`idProducto`),
  ADD KEY `idEstadoProducto` (`idEstadoProducto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idRol` (`idRol`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cargo` (`cargo`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idPedido` (`idPedido`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `encargados`
--
ALTER TABLE `encargados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `estados_mesas`
--
ALTER TABLE `estados_mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados_pedidos`
--
ALTER TABLE `estados_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `estados_productos`
--
ALTER TABLE `estados_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `encargados`
--
ALTER TABLE `encargados`
  ADD CONSTRAINT `encargados_ibfk_1` FOREIGN KEY (`idRol`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD CONSTRAINT `mesas_ibfk_1` FOREIGN KEY (`idEstadoMesa`) REFERENCES `estados_mesas` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`idEstadoPedido`) REFERENCES `estados_pedidos` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_2` FOREIGN KEY (`codigoMesa`) REFERENCES `mesas` (`codigoMesa`) ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_ibfk_3` FOREIGN KEY (`idEncargado`) REFERENCES `encargados` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedidos_productos`
--
ALTER TABLE `pedidos_productos`
  ADD CONSTRAINT `pedidos_productos_ibfk_1` FOREIGN KEY (`idPedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_productos_ibfk_2` FOREIGN KEY (`idProducto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedidos_productos_ibfk_3` FOREIGN KEY (`idEstadoProducto`) REFERENCES `estados_productos` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`idRol`) REFERENCES `roles` (`id`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`idPedido`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
