-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Дек 25 2012 г., 14:30
-- Версия сервера: 5.1.63
-- Версия PHP: 5.3.18

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- База данных: `barbass_typos_new`
--

-- --------------------------------------------------------

--
-- Структура таблицы `messages`
--

CREATE TABLE IF NOT EXISTS `messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_site` int(11) NOT NULL,
  `link` text NOT NULL COMMENT 'Ссылка на страницу',
  `error_text` varchar(100) NOT NULL COMMENT 'Выделенный текст ',
  `comment` varchar(50) DEFAULT NULL COMMENT 'комментарий пользователя',
  `date` datetime NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT '1: исправлена, 0: нет',
  PRIMARY KEY (`id`),
  KEY `id_site` (`id_site`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Сообщения' AUTO_INCREMENT=325 ;

-- --------------------------------------------------------

--
-- Структура таблицы `responsible`
--

CREATE TABLE IF NOT EXISTS `responsible` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_site` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `status` tinyint(1) NOT NULL COMMENT 'Возможность отписать себя с рассылки',
  `date` datetime NOT NULL COMMENT 'Дата регистрации пользователя на сайт',
  PRIMARY KEY (`id`),
  KEY `id_site` (`id_site`),
  KEY `id_user` (`id_user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Зависимость сайтов и ответственных' AUTO_INCREMENT=34 ;

-- --------------------------------------------------------

--
-- Структура таблицы `sites`
--

CREATE TABLE IF NOT EXISTS `sites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `site` varchar(150) NOT NULL COMMENT 'Формат: mysite.ru',
  `date` datetime NOT NULL COMMENT 'Дата регистрации',
  PRIMARY KEY (`id`),
  UNIQUE KEY `site` (`site`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Список сайтов' AUTO_INCREMENT=46 ;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `login` varchar(100) NOT NULL,
  `type` enum('admin','user') NOT NULL DEFAULT 'user' COMMENT 'Тип пользователя',
  `email` varchar(100) NOT NULL,
  `password` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `lastname` varchar(20) NOT NULL,
  `activity` enum('1','0') NOT NULL DEFAULT '0' COMMENT 'Активирован аккаунт',
  `date` datetime NOT NULL COMMENT 'Дата добавления',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='Пользователи системы' AUTO_INCREMENT=55 ;
