-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-06-2022 a las 23:20:42
-- Versión del servidor: 10.4.24-MariaDB
-- Versión de PHP: 8.0.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `comanda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `acciones`
--

CREATE TABLE `acciones` (
  `IdAccion` int(11) NOT NULL,
  `Tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `acciones`
--

INSERT INTO `acciones` (`IdAccion`, `Tipo`) VALUES
(1, 'Login'),
(2, 'Alta'),
(3, 'Baja'),
(4, 'Modificacion');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `area`
--

CREATE TABLE `area` (
  `IdArea` int(11) NOT NULL,
  `Descripcion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `area`
--

INSERT INTO `area` (`IdArea`, `Descripcion`) VALUES
(11, 'Administracion'),
(12, 'Salon'),
(13, 'Barra_Vinos'),
(14, 'Barra_Cerveza'),
(15, 'Cocina'),
(16, 'Candy_Bar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `IdAuditoria` int(11) NOT NULL,
  `IdUsuario` int(11) DEFAULT NULL,
  `idRefUsuario` int(11) DEFAULT NULL,
  `FechaAlta` date NOT NULL,
  `FechaBaja` date DEFAULT NULL,
  `FechaModificacion` date NOT NULL,
  `Hora` varchar(50) NOT NULL,
  `IdAccion` int(11) NOT NULL,
  `IdMesa` int(11) DEFAULT NULL,
  `IdPedido` int(11) DEFAULT NULL,
  `IdProducto` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`IdAuditoria`, `IdUsuario`, `idRefUsuario`, `FechaAlta`, `FechaBaja`, `FechaModificacion`, `Hora`, `IdAccion`, `IdMesa`, `IdPedido`, `IdProducto`) VALUES
(308, 0, 54, '2022-06-25', NULL, '2022-06-25', '02:01:12', 2, NULL, NULL, NULL),
(309, 54, NULL, '2022-06-25', NULL, '2022-06-25', '02:03:00', 1, NULL, NULL, NULL),
(310, 0, 55, '2022-06-25', NULL, '2022-06-25', '02:28:36', 2, NULL, NULL, NULL),
(311, 55, NULL, '2022-06-25', NULL, '2022-06-25', '02:34:55', 1, NULL, NULL, NULL),
(312, 55, NULL, '2022-06-25', NULL, '2022-06-25', '02:54:50', 1, NULL, NULL, NULL),
(313, 0, 56, '2022-06-25', NULL, '2022-06-25', '03:40:35', 2, NULL, NULL, NULL),
(314, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:04:58', 1, NULL, NULL, NULL),
(315, 55, 57, '2022-06-25', NULL, '2022-06-25', '04:05:08', 2, NULL, NULL, NULL),
(316, 55, 58, '2022-06-25', NULL, '2022-06-25', '04:18:44', 2, NULL, NULL, NULL),
(317, 55, 59, '2022-06-25', NULL, '2022-06-25', '04:20:45', 2, NULL, NULL, NULL),
(318, 55, 60, '2022-06-25', NULL, '2022-06-25', '04:21:26', 2, NULL, NULL, NULL),
(319, 55, 61, '2022-06-25', NULL, '2022-06-25', '04:23:12', 2, NULL, NULL, NULL),
(320, 55, 62, '2022-06-25', NULL, '2022-06-25', '04:24:36', 2, NULL, NULL, NULL),
(321, 55, 63, '2022-06-25', NULL, '2022-06-25', '04:27:01', 2, NULL, NULL, NULL),
(322, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:45:50', 2, 11, NULL, NULL),
(323, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:45:55', 2, 12, NULL, NULL),
(324, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:45:57', 2, 13, NULL, NULL),
(325, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:00', 2, 14, NULL, NULL),
(326, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:02', 2, 15, NULL, NULL),
(327, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:04', 2, 16, NULL, NULL),
(328, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:07', 2, 17, NULL, NULL),
(329, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:09', 2, 18, NULL, NULL),
(330, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:11', 2, 19, NULL, NULL),
(331, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:23', 2, 20, NULL, NULL),
(332, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:46:28', 2, 21, NULL, NULL),
(333, 55, NULL, '2022-06-25', NULL, '2022-06-25', '04:55:00', 3, 14, NULL, NULL),
(334, 55, 58, '2022-06-25', NULL, '2022-06-25', '05:29:51', 4, NULL, NULL, NULL),
(335, 55, NULL, '2022-06-25', NULL, '2022-06-25', '05:39:39', 1, NULL, NULL, NULL),
(336, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:39:48', 20, NULL, NULL, NULL),
(337, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:41:08', 20, NULL, NULL, NULL),
(338, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:42:08', 20, NULL, NULL, NULL),
(339, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:48:36', 20, NULL, NULL, NULL),
(340, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:51:29', 20, NULL, NULL, NULL),
(341, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:51:30', 20, NULL, NULL, NULL),
(342, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:53:06', 20, NULL, NULL, NULL),
(343, 55, 55, '2022-06-25', NULL, '2022-06-25', '05:53:40', 20, NULL, NULL, NULL),
(344, 55, NULL, '2022-06-25', NULL, '2022-06-25', '06:01:57', 1, NULL, NULL, NULL),
(345, 55, 55, '2022-06-25', NULL, '2022-06-25', '06:03:09', 2, 12, 41, NULL),
(346, 55, 55, '2022-06-25', NULL, '2022-06-25', '06:15:42', 26, 12, 41, NULL),
(347, 55, 55, '2022-06-25', NULL, '2022-06-25', '06:16:57', 24, 12, 41, NULL),
(348, 55, 55, '2022-06-25', NULL, '2022-06-25', '06:16:59', 25, 12, 41, NULL),
(349, 55, 55, '2022-06-25', NULL, '2022-06-25', '06:17:59', 4, 12, 41, NULL),
(350, 55, 55, '2022-06-25', NULL, '2022-06-25', '06:18:50', 23, 12, 41, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mesas`
--

CREATE TABLE `mesas` (
  `IdMesa` int(11) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Descripcion` varchar(50) NOT NULL,
  `Codigo` varchar(50) NOT NULL,
  `FechaAlta` date NOT NULL,
  `FechaBaja` date DEFAULT NULL,
  `FechaModificacion` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `mesas`
--

INSERT INTO `mesas` (`IdMesa`, `Estado`, `Descripcion`, `Codigo`, `FechaAlta`, `FechaBaja`, `FechaModificacion`) VALUES
(11, 'Libre', 'Mesa1', 'mfiz1pu5jg', '2022-06-25', NULL, '2022-06-25'),
(12, 'Libre', 'Mesa2', 'k2bwd461gi', '2022-06-25', NULL, '2022-06-25'),
(13, 'Cerrada', 'Se le rompio una pata', 'tcvxg7fi60', '2022-06-25', NULL, '2022-06-25'),
(14, 'Libre', 'Mesa4', 'ix5zeu27st', '2022-06-25', '2022-06-25', '2022-06-25'),
(15, 'Libre', 'Mesa5', 'h6sdlqf2yc', '2022-06-25', NULL, '2022-06-25'),
(16, 'Libre', 'Mesa6', 'n17waqf8xk', '2022-06-25', NULL, '2022-06-25'),
(17, 'Libre', 'Mesa7', 'stvon31brx', '2022-06-25', NULL, '2022-06-25'),
(18, 'Libre', 'Mesa8', 'hteswlmrkj', '2022-06-25', NULL, '2022-06-25'),
(20, 'Libre', 'Mesa9', 'eh6l8q4y73', '2022-06-25', NULL, '2022-06-25'),
(21, 'Libre', 'Mesa10', 'xsv9pwch6l', '2022-06-25', NULL, '2022-06-25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `IdPedido` int(11) NOT NULL,
  `IdMesa` int(11) NOT NULL,
  `IdUsuario` int(11) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `FechaAlta` date NOT NULL,
  `FechaModificacion` date NOT NULL,
  `FechaBaja` date DEFAULT NULL,
  `NombreCliente` varchar(50) NOT NULL,
  `CodigoPedido` varchar(50) NOT NULL,
  `PathFoto` varchar(50) NOT NULL,
  `TiempoPreparacion` int(11) NOT NULL,
  `ImporteTotal` int(11) NOT NULL,
  `HoraFin` varchar(50) DEFAULT NULL,
  `PuntuacionMesa` int(11) DEFAULT NULL,
  `Comentario` varchar(66) DEFAULT NULL,
  `PuntuacionMozo` int(11) DEFAULT NULL,
  `PuntuacionCocinero` int(11) DEFAULT NULL,
  `PuntuacionRestaurante` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`IdPedido`, `IdMesa`, `IdUsuario`, `Estado`, `FechaAlta`, `FechaModificacion`, `FechaBaja`, `NombreCliente`, `CodigoPedido`, `PathFoto`, `TiempoPreparacion`, `ImporteTotal`, `HoraFin`, `PuntuacionMesa`, `Comentario`, `PuntuacionMozo`, `PuntuacionCocinero`, `PuntuacionRestaurante`) VALUES
(41, 12, 55, 'CobradoEncuestado', '2022-06-25', '2022-06-25', NULL, 'Stefano', 'jbx67yflgh', './imagenes/jbx67yflgh.png', 7, 1200, '06:16:57', 9, 'Linda atencion y buen lugar.', 10, 6, 9);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidosdetalle`
--

CREATE TABLE `pedidosdetalle` (
  `IdPedidoDetalle` int(11) NOT NULL,
  `IdProducto` int(11) NOT NULL,
  `IdPedido` int(11) NOT NULL,
  `Cantidad` int(11) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `FechaAlta` date NOT NULL,
  `FechaBaja` date DEFAULT NULL,
  `FechaModificacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `pedidosdetalle`
--

INSERT INTO `pedidosdetalle` (`IdPedidoDetalle`, `IdProducto`, `IdPedido`, `Cantidad`, `Estado`, `FechaAlta`, `FechaBaja`, `FechaModificacion`) VALUES
(75, 99, 41, 3, 'Pendiente', '2022-06-25', NULL, '2022-06-25'),
(76, 100, 41, 3, 'Pendiente', '2022-06-25', NULL, '2022-06-25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `IdProducto` int(11) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Stock` int(11) NOT NULL,
  `PrecioUnidad` int(11) NOT NULL,
  `TiempoEspera` int(11) NOT NULL,
  `Area` varchar(50) NOT NULL,
  `TipoProducto` varchar(50) NOT NULL,
  `FechaAlta` date NOT NULL,
  `FechaBaja` date DEFAULT NULL,
  `FechaModificacion` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`IdProducto`, `Nombre`, `Stock`, `PrecioUnidad`, `TiempoEspera`, `Area`, `TipoProducto`, `FechaAlta`, `FechaBaja`, `FechaModificacion`) VALUES
(98, 'Daikiri', 2400, 150, 7, '14', 'Bebida', '2022-06-25', NULL, '2022-06-25'),
(99, 'Schneider', 797, 150, 7, '14', 'Bebida', '2022-06-25', NULL, '2022-06-25'),
(100, 'Quilmes', 797, 250, 7, '14', 'Bebida', '2022-06-25', NULL, '2022-06-25'),
(101, 'Pizza', 3300, 100, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(102, 'Pancho', 750, 100, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(133, 'Hamburguesa', 20, 700, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(134, 'Hamburguesa Doble', 20, 700, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(135, 'Hamburguesa Triple', 20, 800, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(136, 'Hamburguesa Doble c/ cheddar', 20, 790, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(137, 'Hamburguesa Veggie', 20, 900, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(138, 'Hamburguesa Garbanzo', 20, 1000, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(139, 'Corona', 50, 300, 5, '14', 'Bebida', '2022-06-25', NULL, '2022-06-25'),
(140, 'Milanesa', 25, 300, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(141, 'Milanesa Napolitana', 25, 400, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25'),
(142, 'Milanesa Caballo', 25, 500, 15, '15', 'Comida', '2022-06-25', NULL, '2022-06-25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `IdUsuario` int(11) NOT NULL,
  `Usuario` varchar(50) NOT NULL,
  `Clave` varchar(50) NOT NULL,
  `Nombre` varchar(50) NOT NULL,
  `Apellido` varchar(50) NOT NULL,
  `Estado` varchar(50) NOT NULL,
  `Puesto` varchar(50) NOT NULL,
  `FechaAlta` date NOT NULL,
  `FechaBaja` date DEFAULT NULL,
  `FechaModificacion` date NOT NULL,
  `IdUsuarioTipo` int(11) DEFAULT NULL,
  `IdArea` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`IdUsuario`, `Usuario`, `Clave`, `Nombre`, `Apellido`, `Estado`, `Puesto`, `FechaAlta`, `FechaBaja`, `FechaModificacion`, `IdUsuarioTipo`, `IdArea`) VALUES
(55, 'admin', 'clave123', 'Stefano', 'Mugetti', 'Ocupado', 'Socio', '2022-06-25', NULL, '2022-06-25', 6, 11),
(56, 'mozo1', 'clave123', 'Mariano', 'Lopez', 'Activo', 'Mozo', '2022-06-25', NULL, '2022-06-25', 7, 11),
(57, 'mozo2', 'clave123', 'Juani', 'Bruno', 'Activo', 'Mozo', '2022-06-25', NULL, '2022-06-25', 7, 11),
(58, 'mozo3', 'clave123', 'Margarita', 'Rodriguez', 'Suspendido', 'Mozo', '2022-06-25', NULL, '2022-06-25', 7, 11),
(59, 'cocinero', 'clave123', 'Rata', 'Touille', 'Activo', 'Cocinero', '2022-06-25', NULL, '2022-06-25', 10, 15),
(60, 'cocinero2', 'clave123', 'Brad', 'Pitt', 'Activo', 'Cocinero', '2022-06-25', NULL, '2022-06-25', 10, 15),
(61, 'bartender1', 'clave123', 'Brad', 'Pitt', 'Activo', 'Bartender', '2022-06-25', NULL, '2022-06-25', 8, 13),
(62, 'bartender2', 'clave123', 'Ruben', 'Amado', 'Activo', 'Bartender', '2022-06-25', NULL, '2022-06-25', 9, 14),
(63, 'adm1', 'clave123', 'Santino', 'Rufo', 'Activo', 'Bartender', '2022-06-25', NULL, '2022-06-25', 5, 11);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuariotipo`
--

CREATE TABLE `usuariotipo` (
  `IdUsuarioTipo` int(11) NOT NULL,
  `Tipo` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Volcado de datos para la tabla `usuariotipo`
--

INSERT INTO `usuariotipo` (`IdUsuarioTipo`, `Tipo`) VALUES
(5, 'Administrador'),
(6, 'Socio'),
(7, 'Mozo'),
(8, 'Bartender'),
(9, 'Bartender_Cerveza'),
(10, 'Cocinero');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `acciones`
--
ALTER TABLE `acciones`
  ADD PRIMARY KEY (`IdAccion`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`IdAuditoria`);

--
-- Indices de la tabla `mesas`
--
ALTER TABLE `mesas`
  ADD PRIMARY KEY (`IdMesa`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`IdPedido`);

--
-- Indices de la tabla `pedidosdetalle`
--
ALTER TABLE `pedidosdetalle`
  ADD PRIMARY KEY (`IdPedidoDetalle`),
  ADD KEY `pedidos_producto` (`IdProducto`),
  ADD KEY `pedidos_pedidos` (`IdPedido`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`IdProducto`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`IdUsuario`);

--
-- Indices de la tabla `usuariotipo`
--
ALTER TABLE `usuariotipo`
  ADD PRIMARY KEY (`IdUsuarioTipo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `acciones`
--
ALTER TABLE `acciones`
  MODIFY `IdAccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `IdAuditoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=351;

--
-- AUTO_INCREMENT de la tabla `mesas`
--
ALTER TABLE `mesas`
  MODIFY `IdMesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `IdPedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT de la tabla `pedidosdetalle`
--
ALTER TABLE `pedidosdetalle`
  MODIFY `IdPedidoDetalle` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=77;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `IdProducto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=143;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `IdUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=64;

--
-- AUTO_INCREMENT de la tabla `usuariotipo`
--
ALTER TABLE `usuariotipo`
  MODIFY `IdUsuarioTipo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedidosdetalle`
--
ALTER TABLE `pedidosdetalle`
  ADD CONSTRAINT `pedidos_pedidos` FOREIGN KEY (`IdPedido`) REFERENCES `pedidos` (`IdPedido`),
  ADD CONSTRAINT `pedidos_producto` FOREIGN KEY (`IdProducto`) REFERENCES `productos` (`IdProducto`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
