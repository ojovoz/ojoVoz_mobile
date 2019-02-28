-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Servidor: 192.168.86.197
-- Tiempo de generación: 28-02-2019 a las 17:58:00
-- Versión del servidor: 5.5.57-0+deb7u1-log
-- Versión de PHP: 5.3.29-1~dotdeb.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `ovwebserver`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `attachment`
--

CREATE TABLE IF NOT EXISTS `attachment` (
  `attachment_id` bigint(20) unsigned NOT NULL,
  `message_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `filename` text NOT NULL,
  `content_type` varchar(100) NOT NULL DEFAULT '0',
  `original_filename` varchar(255) NOT NULL DEFAULT '',
  `image_width` smallint(6) NOT NULL DEFAULT '0',
  `image_height` smallint(6) NOT NULL DEFAULT '0',
  `latitude` varchar(20) NOT NULL DEFAULT '',
  `longitude` varchar(20) NOT NULL DEFAULT '',
  `gsm_info` varchar(30) NOT NULL DEFAULT '',
  `date_time` varchar(255) NOT NULL DEFAULT '',
  `map_address` varchar(255) NOT NULL DEFAULT '',
  `map_filename` varchar(200) NOT NULL DEFAULT '',
  `is_published` tinyint(1) unsigned NOT NULL DEFAULT '1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='attachments de los mensajes';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `channel`
--

CREATE TABLE IF NOT EXISTS `channel` (
  `channel_id` smallint(5) unsigned NOT NULL,
  `open_closed` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `channel_name` varchar(255) NOT NULL DEFAULT '',
  `channel_folder` varchar(255) NOT NULL DEFAULT '',
  `file_index` bigint(20) NOT NULL DEFAULT '0',
  `channel_mail` varchar(255) NOT NULL DEFAULT '',
  `channel_pass` varchar(255) NOT NULL DEFAULT '',
  `show_time` tinyint(1) NOT NULL DEFAULT '1',
  `show_date` tinyint(1) NOT NULL DEFAULT '1',
  `show_sender` tinyint(1) NOT NULL DEFAULT '1',
  `background_color` varchar(6) NOT NULL DEFAULT 'FFFFFF',
  `text_color` varchar(6) NOT NULL DEFAULT '000000',
  `channel_description` text NOT NULL,
  `channel_description_color` varchar(6) NOT NULL DEFAULT '000000',
  `tag_color` varchar(20) NOT NULL DEFAULT '000000',
  `descriptor_color` varchar(20) NOT NULL DEFAULT '000000',
  `data_color` varchar(6) NOT NULL DEFAULT '000000',
  `legend_color` varchar(20) NOT NULL DEFAULT '000000',
  `is_crono` tinyint(1) NOT NULL DEFAULT '0',
  `is_visible` tinyint(1) NOT NULL DEFAULT '1',
  `is_ascending` tinyint(1) NOT NULL DEFAULT '0',
  `messages_per_page` smallint(6) NOT NULL DEFAULT '10',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `has_thumbnails` tinyint(1) NOT NULL DEFAULT '0',
  `is_study` tinyint(1) NOT NULL DEFAULT '0',
  `is_individual` tinyint(1) NOT NULL DEFAULT '0',
  `show_tags` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `show_descriptors` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tag_mode` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `show_map` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `show_legend` tinyint(1) NOT NULL DEFAULT '0',
  `parent_channel_id` smallint(6) NOT NULL DEFAULT '-1',
  `allow_search` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `tag_minimum_date` date NOT NULL DEFAULT '0000-00-00',
  `has_rss` tinyint(1) NOT NULL DEFAULT '0',
  `channel_pass_edit` varchar(100) NOT NULL DEFAULT '',
  `publish_default` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `color_combination` smallint(5) unsigned NOT NULL DEFAULT '0',
  `phone_id` varchar(100) NOT NULL,
  `tag_list` text NOT NULL
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1 COMMENT='canales de comunicacion';

--
-- Volcado de datos para la tabla `channel`
--

INSERT INTO `channel` (`channel_id`, `open_closed`, `channel_name`, `channel_folder`, `file_index`, `channel_mail`, `channel_pass`, `show_time`, `show_date`, `show_sender`, `background_color`, `text_color`, `channel_description`, `channel_description_color`, `tag_color`, `descriptor_color`, `data_color`, `legend_color`, `is_crono`, `is_visible`, `is_ascending`, `messages_per_page`, `is_active`, `has_thumbnails`, `is_study`, `is_individual`, `show_tags`, `show_descriptors`, `tag_mode`, `show_map`, `show_legend`, `parent_channel_id`, `allow_search`, `tag_minimum_date`, `has_rss`, `channel_pass_edit`, `publish_default`, `color_combination`, `phone_id`, `tag_list`) VALUES
(1, 0, 'General', '', 0, '', '', 1, 1, 1, 'FFFFFF', '000000', '', '000000', '0000FF', 'FF0000', '00FF00', '000000', 1, 1, 0, 10, 0, 0, 0, 0, 1, 0, 0, 0, 0, -1, 0, '0000-00-00', 0, '', 1, 0, '', ''),
(2, 0, '01', '01', 24, 'xxx@xxx.net', 'xxx', 1, 1, 1, 'FFFFFF', '000000', '', '000000', '0000FF', 'FF0000', '00FF00', '000000', 0, 1, 0, 10, 1, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, '0000-00-00', 0, 'xxx', 1, 0, '01', 'tag1;tag2;tag3');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comment`
--

CREATE TABLE IF NOT EXISTS `comment` (
  `comment_id` bigint(20) unsigned NOT NULL,
  `message_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `user_id` bigint(20) unsigned NOT NULL DEFAULT '0',
  `comment_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `comment_text` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `global`
--

CREATE TABLE IF NOT EXISTS `global` (
  `global_id` int(10) unsigned NOT NULL,
  `global_variable` varchar(200) NOT NULL DEFAULT '',
  `value` varchar(200) NOT NULL DEFAULT ''
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `global`
--

INSERT INTO `global` (`global_id`, `global_variable`, `value`) VALUES
(1, 'default_channel_id', '1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `message`
--

CREATE TABLE IF NOT EXISTS `message` (
  `message_id` bigint(20) unsigned NOT NULL,
  `channel_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `message_text` text NOT NULL,
  `message_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `message_sender` varchar(255) NOT NULL DEFAULT '',
  `message_subject` varchar(255) NOT NULL DEFAULT '',
  `sender_email` varchar(255) NOT NULL DEFAULT '',
  `message_order` smallint(6) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=latin1 COMMENT='mensajes de los canales';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tag`
--

CREATE TABLE IF NOT EXISTS `tag` (
  `tag_id` mediumint(10) unsigned NOT NULL,
  `tag_name` varchar(255) NOT NULL DEFAULT '',
  `times_clicked` smallint(5) NOT NULL DEFAULT '0',
  `tag_group_id` bigint(20) NOT NULL DEFAULT '-1',
  `in_map` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `color_in_map` char(2) NOT NULL DEFAULT '16',
  `in_megafone` tinyint(4) NOT NULL DEFAULT '0',
  `is_study` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tag_group`
--

CREATE TABLE IF NOT EXISTS `tag_group` (
  `tag_group_id` bigint(20) NOT NULL,
  `tag_group_name` varchar(255) NOT NULL DEFAULT '0',
  `color_in_map` char(2) NOT NULL DEFAULT '16',
  `in_map` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tag_x_channel`
--

CREATE TABLE IF NOT EXISTS `tag_x_channel` (
  `tag_x_channel_id` mediumint(8) unsigned NOT NULL,
  `channel_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `tag_id` mediumint(8) NOT NULL DEFAULT '-1',
  `tag_group_id` bigint(20) NOT NULL DEFAULT '-1'
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tag_x_language`
--

CREATE TABLE IF NOT EXISTS `tag_x_language` (
  `tag_x_language_id` bigint(20) unsigned NOT NULL,
  `tag_id` mediumint(8) NOT NULL DEFAULT '0',
  `language_id` smallint(5) unsigned NOT NULL DEFAULT '0',
  `translation` varchar(200) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tag_x_message`
--

CREATE TABLE IF NOT EXISTS `tag_x_message` (
  `tag_x_message_id` bigint(20) unsigned NOT NULL,
  `tag_id` bigint(20) NOT NULL DEFAULT '0',
  `message_id` bigint(20) NOT NULL DEFAULT '0',
  `from_mobile` tinyint(1) unsigned NOT NULL DEFAULT '0'
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `attachment`
--
ALTER TABLE `attachment`
  ADD PRIMARY KEY (`attachment_id`);

--
-- Indices de la tabla `channel`
--
ALTER TABLE `channel`
  ADD PRIMARY KEY (`channel_id`);

--
-- Indices de la tabla `comment`
--
ALTER TABLE `comment`
  ADD PRIMARY KEY (`comment_id`);

--
-- Indices de la tabla `global`
--
ALTER TABLE `global`
  ADD PRIMARY KEY (`global_id`);

--
-- Indices de la tabla `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`);

--
-- Indices de la tabla `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`tag_id`);

--
-- Indices de la tabla `tag_group`
--
ALTER TABLE `tag_group`
  ADD PRIMARY KEY (`tag_group_id`);

--
-- Indices de la tabla `tag_x_channel`
--
ALTER TABLE `tag_x_channel`
  ADD PRIMARY KEY (`tag_x_channel_id`);

--
-- Indices de la tabla `tag_x_language`
--
ALTER TABLE `tag_x_language`
  ADD PRIMARY KEY (`tag_x_language_id`);

--
-- Indices de la tabla `tag_x_message`
--
ALTER TABLE `tag_x_message`
  ADD PRIMARY KEY (`tag_x_message_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `attachment`
--
ALTER TABLE `attachment`
  MODIFY `attachment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `channel`
--
ALTER TABLE `channel`
  MODIFY `channel_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT de la tabla `comment`
--
ALTER TABLE `comment`
  MODIFY `comment_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `global`
--
ALTER TABLE `global`
  MODIFY `global_id` int(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT de la tabla `message`
--
ALTER TABLE `message`
  MODIFY `message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=13;
--
-- AUTO_INCREMENT de la tabla `tag`
--
ALTER TABLE `tag`
  MODIFY `tag_id` mediumint(10) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT de la tabla `tag_group`
--
ALTER TABLE `tag_group`
  MODIFY `tag_group_id` bigint(20) NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `tag_x_channel`
--
ALTER TABLE `tag_x_channel`
  MODIFY `tag_x_channel_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `tag_x_language`
--
ALTER TABLE `tag_x_language`
  MODIFY `tag_x_language_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT de la tabla `tag_x_message`
--
ALTER TABLE `tag_x_message`
  MODIFY `tag_x_message_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=16;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
