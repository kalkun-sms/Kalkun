-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 30, 2010 at 04:16 AM
-- Server version: 5.1.37
-- PHP Version: 5.3.0

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `kalkun`
--

-- --------------------------------------------------------

--
-- Table structure for table `member`
--

CREATE TABLE IF NOT EXISTS `member` (
  `id_member` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` text NOT NULL,
  `reg_date` datetime NOT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

-- --------------------------------------------------------

--
-- Table structure for table `sms_used`
--

CREATE TABLE IF NOT EXISTS `sms_used` (
  `id_sms_used` int(11) NOT NULL AUTO_INCREMENT,
  `sms_date` date NOT NULL,
  `id_user` int(11) NOT NULL,
  `sms_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_sms_used`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=26 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(12) NOT NULL,
  `realname` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `level` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `username_2` (`username`),
  UNIQUE KEY `phone_number` (`phone_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=19 ;

-- --------------------------------------------------------

--
-- Table structure for table `user_inbox`
--

CREATE TABLE IF NOT EXISTS `user_inbox` (
  `id_inbox` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `trash` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_inbox`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_outbox`
--

CREATE TABLE IF NOT EXISTS `user_outbox` (
  `id_outbox` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_outbox`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_sentitems`
--

CREATE TABLE IF NOT EXISTS `user_sentitems` (
  `id_sentitems` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `trash` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_sentitems`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `user_settings`
--

CREATE TABLE IF NOT EXISTS `user_settings` (
  `id_user` int(11) NOT NULL,
  `theme` varchar(10) NOT NULL DEFAULT 'blue',
  `signature` varchar(50) NOT NULL,
  `permanent_delete` enum('true','false') NOT NULL DEFAULT 'false',
  `paging` int(2) NOT NULL DEFAULT '10',
  `bg_image` varchar(50) NOT NULL,
  `delivery_report` enum('default','yes','no') NOT NULL DEFAULT 'default',
  `language` varchar(20) NOT NULL DEFAULT 'english',
  `conversation_sort` enum('asc','desc') NOT NULL DEFAULT 'asc',
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


--
-- ALTER
--

ALTER TABLE `pbk` ADD `id_user` INT( 11 ) NOT NULL;
ALTER TABLE `pbk_groups` ADD `id_user` INT( 11 ) NOT NULL;
ALTER TABLE `pbk_groups` CHANGE `Name` `GroupName` TEXT CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

RENAME TABLE `folders` TO `user_folders`;
ALTER TABLE `user_folders` ADD `id_user` INT( 11 ) NOT NULL;

--
-- UPDATE
--
UPDATE `pbk` SET `id_user`='1';
UPDATE `pbk_groups` SET `id_user`='1';
UPDATE `user_folders` SET `id_user`='1';
UPDATE `user_folders` SET `id_user`='0' where `id_folder` BETWEEN 1 and 5;


--
-- DROP
-- 
DROP TABLE `plugin`;
DROP TABLE `plugin_blacklist_number`;
DROP TABLE `plugin_server_alert`;


--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id_user`, `theme`, `signature`, `permanent_delete`, `paging`, `bg_image`, `delivery_report`, `language`, `conversation_sort`) VALUES
(1, 'green', 'false;--\nPut your signature here', 'false', 20, 'true;background.jpg', 'default', 'english', 'asc');
