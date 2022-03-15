-- phpMyAdmin SQL Dump
-- version 3.2.0.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Feb 22, 2011 at 05:38 PM
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
-- Table structure for table `kalkun`
--

CREATE TABLE IF NOT EXISTS `kalkun` (
  `version` text NOT NULL
) DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sms_used`
--

CREATE TABLE IF NOT EXISTS `sms_used` (
  `id_sms_used` int(11) NOT NULL AUTO_INCREMENT,
  `sms_date` date NOT NULL,
  `id_user` int(11) NOT NULL,
  `out_sms_count` int(11) NOT NULL DEFAULT '0',
  `in_sms_count` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_sms_used`)
)  DEFAULT CHARSET=utf8mb4 ;

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id_user` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(12) NOT NULL,
  `realname` varchar(100) NOT NULL,
  `password` varchar(191) NOT NULL,
  `phone_number` varchar(15) NOT NULL,
  `level` enum('admin','user') NOT NULL DEFAULT 'user',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `phone_number` (`phone_number`)
)  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `username`, `realname`, `password`, `phone_number`, `level`) VALUES
(1, 'kalkun', 'Kalkun SMS', '$2y$10$sIXe0JiaTIOsC7OOnox5t.deuJwZoawd5QKpQlSNfywziTDHpmmyy', '123456789', 'admin');


-- --------------------------------------------------------

--
-- Table structure for table `user_folders`
--

CREATE TABLE IF NOT EXISTS `user_folders` (
  `id_folder` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_folder`)
)  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=11 ;

--
-- Dumping data for table `user_folders`
--

INSERT INTO `user_folders` (`id_folder`, `name`, `id_user`) VALUES
(1, 'inbox', 0),
(2, 'outbox', 0),
(3, 'sent_items', 0),
(4, 'draft', 0),
(5, 'Trash', 0),
(6, 'Spam', 0);

-- --------------------------------------------------------

--
-- Table structure for table `user_inbox`
--

CREATE TABLE IF NOT EXISTS `user_inbox` (
  `id_inbox` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `trash` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_inbox`)
) DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_outbox`
--

CREATE TABLE IF NOT EXISTS `user_outbox` (
  `id_outbox` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_outbox`)
) DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_sentitems`
--

CREATE TABLE IF NOT EXISTS `user_sentitems` (
  `id_sentitems` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `trash` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_sentitems`)
) DEFAULT CHARSET=utf8mb4;

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
  `country_code` varchar(2) NOT NULL DEFAULT 'US',
  PRIMARY KEY (`id_user`)
) DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_settings`
--

INSERT INTO `user_settings` (`id_user`, `theme`, `signature`, `permanent_delete`, `paging`, `bg_image`, `delivery_report`, `language`, `conversation_sort`) VALUES
(1, 'green', 'false;--\nPut your signature here', 'false', 20, 'true;background.jpg', 'default' , 'english', 'asc');


-- --------------------------------------------------------

--
-- Alter table structure for table `inbox`
--

ALTER TABLE `inbox` ADD `id_folder` INT( 11 ) NOT NULL DEFAULT '1',
ADD `readed` ENUM( 'false', 'true' ) NOT NULL DEFAULT 'false';


-- --------------------------------------------------------

--
-- Alter table structure for table `sentitems`
--

ALTER TABLE `sentitems` ADD `id_folder` INT( 11 ) NOT NULL DEFAULT '3';


-- --------------------------------------------------------

--
-- Table structure for table `user_group`
--


CREATE TABLE IF NOT EXISTS `user_group` (
  `id_group` int(11) NOT NULL AUTO_INCREMENT,
  `id_pbk` int(11) NOT NULL,
  `id_pbk_groups` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  PRIMARY KEY (`id_group`)
)  DEFAULT CHARSET=utf8mb4 ;


-- --------------------------------------------------------

--
-- Table structure for table `user_templates`
--

CREATE TABLE IF NOT EXISTS `user_templates` (
  `id_template` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `Name` varchar(64) NOT NULL,
  `Message` text NOT NULL,
  PRIMARY KEY (`id_template`)
)  DEFAULT CHARSET=utf8mb4 ;


-- --------------------------------------------------------

--
-- Table structure for table `b8_wordlist` v3 (for b8 >= v0.6)
--

CREATE TABLE `b8_wordlist` (
  `token` varchar(190) character set utf8mb4 collate utf8mb4_bin NOT NULL,
  `count_ham` int unsigned default NULL,
  `count_spam` int unsigned default NULL,
  PRIMARY KEY (`token`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `b8_wordlist` (`token`, `count_ham`) VALUES ('b8*dbversion', '3');
INSERT INTO `b8_wordlist` (`token`, `count_ham`, `count_spam`) VALUES ('b8*texts', '0', '0');


-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `plugins` (
  `plugin_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_system_name` varchar(191) NOT NULL,
  `plugin_name` varchar(191) NOT NULL,
  `plugin_uri` varchar(120) DEFAULT NULL,
  `plugin_version` varchar(30) NOT NULL,
  `plugin_description` text,
  `plugin_author` varchar(120) DEFAULT NULL,
  `plugin_author_uri` varchar(120) DEFAULT NULL,
  `plugin_data` longtext,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `plugin_index` (`plugin_system_name`) USING BTREE
)  DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `user_forgot_password`
--

CREATE TABLE IF NOT EXISTS `user_forgot_password` (
  `id_user` int(11) NOT NULL,
  `token` varchar(191) NOT NULL,
  `valid_until` datetime NOT NULL,
  PRIMARY KEY (`id_user`)
) DEFAULT CHARSET=utf8mb4;


-- --------------------------------------------------------

--
-- Table structure for table `user_filters`
--

CREATE TABLE IF NOT EXISTS `user_filters` (
  `id_filter` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `from` varchar(15) NOT NULL,
  `has_the_words` varchar(50) NOT NULL,
  `id_folder` int(11) NOT NULL,
  PRIMARY KEY (`id_filter`)
)  DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
-- see: https://codeigniter.com/userguide3/libraries/sessions.html#database-driver
--

CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
);
 
