-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-12-2019 a las 12:29:05
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
  `nombre` varchar(60) COLLATE utf8_spanish2_ci NOT NULL,
  `apellido` varchar(60) COLLATE utf8_spanish2_ci NOT NULL,
  `usuario` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  `idRol` int(2) NOT NULL,
  `clave` varchar(60) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `encargados`
--

INSERT INTO `encargados` (`id`, `nombre`, `apellido`, `usuario`, `idRol`, `clave`, `updated_at`, `created_at`) VALUES
(1, 'Leandro', 'Egea', 'leandroe', 3, 'pass', '2019-11-13 18:41:26', '2019-11-09 19:16:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_mesa`
--

CREATE TABLE `estados_mesa` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(40) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `estados_mesa`
--

INSERT INTO `estados_mesa` (`id`, `descripcion`, `updated_at`, `created_at`) VALUES
(1, 'con cliente esperando', '2019-11-09 19:20:31', '2019-11-09 19:20:31'),
(2, 'con clientes comiendo', '2019-11-09 19:22:03', '2019-11-09 19:22:03'),
(3, 'con clientes pagando', '2019-11-09 19:23:13', '2019-11-09 19:23:13'),
(4, 'cerrada', '2019-11-09 19:23:23', '2019-11-09 19:23:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_pedidos`
--

CREATE TABLE `estados_pedidos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `estados_pedidos`
--

INSERT INTO `estados_pedidos` (`id`, `descripcion`, `updated_at`, `created_at`) VALUES
(1, 'en preparacion', '2019-11-09 19:29:48', '2019-11-09 19:29:48'),
(2, 'listo para servir', '2019-11-09 19:30:16', '2019-11-09 19:30:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_productos`
--

CREATE TABLE `estados_productos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `estados_productos`
--

INSERT INTO `estados_productos` (`id`, `descripcion`, `updated_at`, `created_at`) VALUES
(1, 'pendiente', '2019-11-09 19:24:39', '2019-11-09 19:24:39'),
(2, 'en preparacion', '2019-11-09 19:24:55', '2019-11-09 19:24:55'),
(3, 'listo para servir', '2019-11-09 19:25:23', '2019-11-09 19:25:23');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `id` int(11) NOT NULL,
  `codigoMesa` varchar(60) COLLATE utf8_spanish2_ci NOT NULL,
  `idEstadoMesa` int(2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`id`, `codigoMesa`, `idEstadoMesa`, `updated_at`, `created_at`) VALUES
(1, 'MSA01', 2, '2019-11-16 00:03:45', '2019-11-16 00:01:34'),
(2, 'MSA02', 1, '2019-11-16 00:03:45', '2019-11-16 00:01:34'),
(3, 'MSA03', 1, '2019-11-16 00:03:45', '2019-11-16 00:01:34'),
(4, 'MSA04', 1, '2019-11-16 00:03:45', '2019-11-16 00:01:34'),
(5, 'MSA05', 1, '2019-12-02 09:36:50', '2019-12-02 09:23:54'),
(6, 'MSA06', 4, '2019-12-02 09:45:37', '2019-12-02 09:45:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `idEstadoPedido` int(2) NOT NULL,
  `codigoPedido` varchar(10) COLLATE utf8_spanish2_ci NOT NULL,
  `codigoMesa` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `idEncargado` int(2) NOT NULL,
  `productos` varchar(100) COLLATE utf8_spanish2_ci NOT NULL,
  `nombreCliente` varchar(40) COLLATE utf8_spanish2_ci NOT NULL,
  `imagen` varchar(200) COLLATE utf8_spanish2_ci NOT NULL,
  `tiempo` int(4) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos_productos`
--

CREATE TABLE `pedidos_productos` (
  `idPedido` int(11) NOT NULL,
  `idProducto` int(11) NOT NULL,
  `idEstadoProducto` int(2) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `pedidos_productos`
--

INSERT INTO `pedidos_productos` (`idPedido`, `idProducto`, `idEstadoProducto`, `updated_at`, `created_at`) VALUES
(1, 1, 1, '2019-11-16 00:03:45', '2019-11-16 00:03:45'),
(1, 1, 1, '2019-11-16 00:03:45', '2019-11-16 00:03:45'),
(1, 8, 1, '2019-11-16 00:03:45', '2019-11-16 00:03:45'),
(1, 10, 1, '2019-11-16 00:03:45', '2019-11-16 00:03:45'),
(1, 9, 1, '2019-11-16 00:03:45', '2019-11-16 00:03:45');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `descripcion` varchar(60) COLLATE utf8_spanish2_ci NOT NULL,
  `tipo` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
  `precio` int(10) NOT NULL,
  `idRol` int(11) NOT NULL,
  `tiempoPreparacion` int(11) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `descripcion`, `tipo`, `precio`, `idRol`, `tiempoPreparacion`, `updated_at`, `created_at`) VALUES
(1, 'Coca-Cola', 'bebida', 80, 5, 3, '2019-12-02 11:20:56', '2019-12-02 11:20:56'),
(2, 'Fanta', 'bebida', 90, 5, 3, '2019-12-02 11:21:12', '2019-12-02 11:21:12'),
(3, 'Paso de los Toros', 'bebida', 75, 5, 3, '2019-12-02 11:21:38', '2019-12-02 11:21:38'),
(4, 'Cafe', 'bebida', 90, 5, 5, '2019-12-02 11:21:51', '2019-12-02 11:21:51'),
(5, 'Agua Sin Gas', 'bebida', 60, 5, 3, '2019-12-02 11:22:59', '2019-12-02 11:22:59'),
(6, 'Agua Con Gas', 'bebida', 70, 5, 3, '2019-12-02 11:23:19', '2019-12-02 11:23:19'),
(7, 'Milanesa con Fritas', 'comida', 300, 1, 15, '2019-12-02 11:23:45', '2019-12-02 11:23:45'),
(8, 'Tallarines', 'comida', 250, 1, 16, '2019-12-02 11:27:56', '2019-12-02 11:27:56'),
(10, 'Pollo', 'comida', 240, 1, 15, '2019-12-02 11:25:32', '2019-12-02 11:25:32'),
(11, 'Vino tinto', 'vino', 300, 4, 6, '2019-11-12 19:43:05', '2019-11-12 15:43:05'),
(12, 'Racion de Papas Fritas', 'comida', 150, 1, 15, '2019-12-02 11:28:07', '2019-12-02 11:28:07'),
(14, 'Cuba Libre', 'trago', 200, 4, 3, '2019-12-02 11:26:31', '2019-12-02 11:26:31'),
(15, 'Cerveza', 'cerveza', 150, 2, 6, '2019-11-12 19:43:51', '2019-11-12 15:43:51'),
(18, 'Asado para Dos\r\n', 'comida', 500, 1, 12, '2019-12-02 11:27:12', '2019-12-02 11:27:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `cargo` varchar(20) COLLATE utf8_spanish2_ci NOT NULL,
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
  `precioTotal` int(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `encargados`
--
ALTER TABLE `encargados`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estados_mesa`
--
ALTER TABLE `estados_mesa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estados_pedidos`
--
ALTER TABLE `estados_pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `estados_productos`
--
ALTER TABLE `estados_productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `encargados`
--
ALTER TABLE `encargados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `estados_mesa`
--
ALTER TABLE `estados_mesa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `estados_pedidos`
--
ALTER TABLE `estados_pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `estados_productos`
--
ALTER TABLE `estados_productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
