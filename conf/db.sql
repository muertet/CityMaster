-- phpMyAdmin SQL Dump
-- version 4.2.10
-- http://www.phpmyadmin.net
--

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `citymaster`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `app`
--

CREATE TABLE IF NOT EXISTS `app` (
`id` int(10) NOT NULL,
  `secret` varchar(120) NOT NULL,
  `name` varchar(120) NOT NULL,
  `description` text NOT NULL,
  `uid` int(10) NOT NULL,
  `url` varchar(120) NOT NULL,
  `official` int(1) NOT NULL,
  `status` int(1) NOT NULL DEFAULT '1',
  `date` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `app`
--

INSERT INTO `app` (`id`, `secret`, `name`, `description`, `uid`, `url`, `official`, `status`, `date`) VALUES
(1, '2345r6tujyhtg', 'Main App', 'Used for official game', 1, '', 1, 1, '2013-12-13 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `authtoken`
--

CREATE TABLE IF NOT EXISTS `authtoken` (
`id` int(10) NOT NULL,
  `app` int(10) NOT NULL,
  `token` varchar(120) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `authtoken`
--

INSERT INTO `authtoken` (`id`, `app`, `token`, `date`) VALUES
(0, 1, '4b6a0d986b0e0893eb1463dbd3fb7cde', '2014-06-15 15:20:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `building`
--

CREATE TABLE IF NOT EXISTS `building` (
`id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `public` int(1) NOT NULL DEFAULT '0',
  `type` int(1) NOT NULL,
  `build_delay` float NOT NULL DEFAULT '45',
  `upgrade_delay` float NOT NULL DEFAULT '45',
  `storage` int(11) NOT NULL DEFAULT '0',
  `purchase` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `building`
--

INSERT INTO `building` (`id`, `name`, `public`, `type`, `build_delay`, `upgrade_delay`, `storage`, `purchase`) VALUES
(1, 'OIL_RIG', 0, 1, 45, 45, 1000, 1),
(2, 'IRON_MINE', 0, 1, 45, 45, 1000, 1),
(3, 'RUBBER_PLANTATION', 0, 1, 45, 45, 1500, 1),
(4, 'SALTPETER_MINE', 0, 1, 45, 45, 1000, 1),
(5, 'ALUMINIUM_MINE', 0, 1, 45, 45, 1000, 1),
(6, 'GOLD_MINE', 0, 1, 0, 450, 50, 0),
(7, 'TANK_FACTORY', 0, 2, 45, 45, 50, 1),
(8, 'PLANE_FACTORY', 0, 2, 45, 45, 50, 0),
(9, 'VEHICLE_FACTORY', 0, 2, 45, 45, 60, 1),
(10, 'UNIT_FACTORY', 0, 2, 45, 45, 20, 1),
(11, 'WAREHOUSE', 0, 3, 45, 45, 3000, 1),
(12, 'GARAGE', 0, 3, 45, 45, 20, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donation`
--

CREATE TABLE IF NOT EXISTS `donation` (
`id` int(10) NOT NULL,
  `building` int(10) NOT NULL,
  `level` int(2) NOT NULL DEFAULT '0',
  `amount` int(10) NOT NULL,
  `status` int(1) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `donation`
--

INSERT INTO `donation` (`id`, `building`, `level`, `amount`, `status`, `date`) VALUES
(1, 9, 0, 21, 0, '2014-07-31 10:21:55'),
(2, 13, 0, 29, 0, '2014-08-03 18:02:44'),
(3, 14, 0, 0, 0, '2014-08-05 10:06:13'),
(4, 2631, 0, 0, 0, '2014-08-14 09:32:51'),
(5, 2632, 0, 0, 0, '2014-09-15 09:20:10');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donation_log`
--

CREATE TABLE IF NOT EXISTS `donation_log` (
`id` int(10) NOT NULL,
  `building` int(10) NOT NULL,
  `level` int(2) NOT NULL,
  `amount` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `donation_log`
--

INSERT INTO `donation_log` (`id`, `building`, `level`, `amount`, `uid`, `date`) VALUES
(1, 9, 0, 3, 1, '2014-07-31 12:08:06'),
(2, 13, 0, 29, 1, '2014-08-05 10:06:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `group`
--

CREATE TABLE IF NOT EXISTS `group` (
`id` int(11) NOT NULL,
  `name` varchar(120) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `group`
--

INSERT INTO `group` (`id`, `name`, `date`) VALUES
(1, 'TViso', '2014-06-28 13:56:48'),
(2, 'Series.ly', '2014-06-28 14:00:33'),
(3, 'Patatabrava', '2014-07-08 08:48:41'),
(4, 'Food2u', '2014-07-08 08:50:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `item`
--

CREATE TABLE IF NOT EXISTS `item` (
`id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `attack` int(11) NOT NULL DEFAULT '0',
  `defense` int(11) NOT NULL DEFAULT '0',
  `salable` int(1) NOT NULL DEFAULT '1',
  `vehicle` int(1) NOT NULL DEFAULT '0',
  `building` int(2) NOT NULL,
  `date` date NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `item`
--

INSERT INTO `item` (`id`, `name`, `attack`, `defense`, `salable`, `vehicle`, `building`, `date`) VALUES
(1, 'CHASIS', 0, 0, 1, 0, 8, '2014-08-03'),
(2, 'MOTOR_BASIC', 0, 0, 1, 0, 8, '2014-08-03'),
(3, 'WHEEL_VEHICLE', 0, 0, 1, 0, 8, '2014-08-03'),
(4, 'JEEP', 40, 20, 1, 1, 8, '0000-00-00'),
(5, 'JEEP_BLINDED', 40, 80, 1, 1, 8, '0000-00-00'),
(6, 'GLASS_BULLETPROOF', 0, 0, 1, 1, 8, '2014-08-04'),
(7, 'TANK_PANZER', 40, 20, 1, 1, 7, '2014-09-24'),
(8, 'TANK_KUZA', 40, 19, 1, 1, 7, '2014-09-24'),
(9, 'oil', 0, 0, 1, 0, 1, '2014-10-02'),
(10, 'iron', 0, 0, 1, 0, 2, '2014-10-02'),
(11, 'RUBBER', 0, 0, 1, 0, 3, '2014-10-02'),
(12, 'SALTPETER', 0, 0, 1, 0, 4, '2014-10-02'),
(13, 'ALUMINIUM', 0, 0, 1, 0, 5, '2014-10-02'),
(14, 'GOLD', 0, 0, 1, 0, 6, '2014-10-02');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `market`
--

CREATE TABLE IF NOT EXISTS `market` (
`id` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` int(11) NOT NULL,
  `status` int(1) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `market`
--

INSERT INTO `market` (`id`, `item`, `uid`, `quantity`, `price`, `status`, `date`) VALUES
(1, 1, 2, 193, 520, 1, '2014-08-03 14:49:28'),
(2, 2, 2, 297, 131, 1, '2014-08-03 14:49:28'),
(3, 3, 2, 232, 100, 1, '2014-08-03 14:49:28'),
(4, 4, 2, 293, 128, 1, '2014-09-13 13:26:29'),
(5, 5, 2, 121, 243, 1, '2014-09-13 13:26:29'),
(6, 6, 2, 81, 77, 1, '2014-09-13 13:26:29'),
(7, 1, 3, 46, 241, 1, '2014-09-13 13:26:29'),
(8, 2, 3, 184, 145, 1, '2014-09-13 13:26:29'),
(9, 3, 3, 92, 83, 1, '2014-09-13 13:26:29'),
(10, 4, 3, 267, 129, 1, '2014-09-13 13:26:29'),
(11, 5, 3, 286, 65, 1, '2014-09-13 13:26:29'),
(12, 6, 3, 256, 283, 1, '2014-09-13 13:26:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

CREATE TABLE IF NOT EXISTS `user` (
`id` int(10) NOT NULL,
  `nick` varchar(120) NOT NULL,
  `email` varchar(150) NOT NULL,
  `date` datetime NOT NULL,
  `status` int(1) NOT NULL,
  `password` varchar(120) NOT NULL,
  `image` varchar(300) NOT NULL,
  `money` int(11) NOT NULL DEFAULT '0',
  `gold` int(11) NOT NULL DEFAULT '0',
  `referrer` int(11) DEFAULT NULL,
  `lat` float NOT NULL,
  `lng` float NOT NULL,
  `lastAccess` datetime NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `user`
--

INSERT INTO `user` (`id`, `nick`, `email`, `date`, `status`, `password`, `image`, `money`, `gold`, `referrer`, `lat`, `lng`, `lastAccess`) VALUES
(3, 'volvo', 'basura2@yopmail.com', '2014-08-03 20:38:45', 1, 'cd4f7a55c198995044bcc81bba0044c4', '1', 3741, 0, NULL, 41.5548, 1.74433, '0000-00-00 00:00:00'),
(4, 'toyota', 'toyota@yopmail.com', '2014-08-03 20:45:20', 1, 'cd4f7a55c198995044bcc81bba0044c4', '1', 3500, 0, NULL, 41.385, 2.17331, '0000-00-00 00:00:00'),
(2, 'seat', 'basura@yopmail.com', '2014-08-02 13:45:05', 1, 'cd4f7a55c198995044bcc81bba0044c4', '1', 5002, 0, NULL, 41.5548, 1.74433, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usertoken`
--

CREATE TABLE IF NOT EXISTS `usertoken` (
`id` int(10) NOT NULL,
  `app` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `token` varchar(120) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_group`
--

CREATE TABLE IF NOT EXISTS `user_group` (
`id` int(10) NOT NULL,
  `uid` int(10) NOT NULL,
  `group` int(2) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `user_group`
--

INSERT INTO `user_group` (`id`, `uid`, `group`, `date`) VALUES
(1, 17, 1, '2014-06-29 20:26:37'),
(2, 15, 1, '2014-06-29 20:26:45'),
(3, 23, 1, '2014-06-29 20:26:53'),
(4, 1, 2, '2014-06-29 20:27:31'),
(5, 19, 2, '2014-06-29 20:27:44'),
(6, 25, 2, '2014-06-29 20:27:54'),
(7, 15, 2, '2014-06-30 10:56:15'),
(8, 27, 3, '2014-07-08 08:48:53'),
(9, 16, 3, '2014-07-08 08:48:58'),
(10, 20, 3, '2014-07-08 08:49:03'),
(11, 22, 3, '2014-07-08 08:49:17'),
(12, 18, 3, '2014-07-08 08:49:26'),
(13, 21, 3, '2014-07-08 08:49:33'),
(14, 26, 4, '2014-07-08 08:50:50'),
(15, 17, 4, '2014-07-08 08:50:55'),
(16, 24, 2, '2014-07-08 08:53:50'),
(17, 13, 2, '2014-07-08 08:54:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_inventory`
--

CREATE TABLE IF NOT EXISTS `user_inventory` (
`id` int(11) NOT NULL,
  `uid` int(11) NOT NULL,
  `item` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `health` int(3) NOT NULL,
  `date` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `app`
--
ALTER TABLE `app`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `authtoken`
--
ALTER TABLE `authtoken`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `building`
--
ALTER TABLE `building`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `donation`
--
ALTER TABLE `donation`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `donation_log`
--
ALTER TABLE `donation_log`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `group`
--
ALTER TABLE `group`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `item`
--
ALTER TABLE `item`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `market`
--
ALTER TABLE `market`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user`
--
ALTER TABLE `user`
 ADD UNIQUE KEY `nick` (`nick`,`email`), ADD KEY `uid` (`id`);

--
-- Indices de la tabla `usertoken`
--
ALTER TABLE `usertoken`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_group`
--
ALTER TABLE `user_group`
 ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `user_inventory`
--
ALTER TABLE `user_inventory`
 ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `app`
--
ALTER TABLE `app`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `authtoken`
--
ALTER TABLE `authtoken`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `building`
--
ALTER TABLE `building`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT de la tabla `donation`
--
ALTER TABLE `donation`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT de la tabla `donation_log`
--
ALTER TABLE `donation_log`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `group`
--
ALTER TABLE `group`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT de la tabla `item`
--
ALTER TABLE `item`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT de la tabla `market`
--
ALTER TABLE `market`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT de la tabla `user`
--
ALTER TABLE `user`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=48;
--
-- AUTO_INCREMENT de la tabla `usertoken`
--
ALTER TABLE `usertoken`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `user_group`
--
ALTER TABLE `user_group`
MODIFY `id` int(10) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=18;
--
-- AUTO_INCREMENT de la tabla `user_inventory`
--
ALTER TABLE `user_inventory`
MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
