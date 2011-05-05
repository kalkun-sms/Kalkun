-- Kalkun upgrade from 0.3 to 0.4
 
ALTER TABLE  `sms_used` CHANGE  `sms_count`  `out_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'
ALTER TABLE  `sms_used` ADD  `in_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'

ALTER TABLE `pbk` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';
ALTER TABLE `pbk_groups` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';

-- move and insert spam folder at location 6 
UPDATE `inbox` set id_folder = id_folder + 1 where id_folder > 5 
UPDATE `sentitems` set id_folder = id_folder + 1 where id_folder > 5
UPDATE `user_folders` set id_folder = id_folder + 1 where id_folder > 5
INSERT INTO `user_folders` (`id_folder`, `name`, `id_user`) VALUES (6, 'Spam', 0);

CREATE TABLE `b8_wordlist` (
  `token` varchar(255) character set utf8 collate utf8_bin NOT NULL,
  `count` varchar(255) default NULL,
  PRIMARY KEY  (`token`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

INSERT INTO `b8_wordlist` VALUES ('bayes*dbversion', '2');
INSERT INTO `b8_wordlist` VALUES ('bayes*texts.ham', '0');
INSERT INTO `b8_wordlist` VALUES ('bayes*texts.spam', '0');

-- --------------------------------------------------------

--
-- Table structure for table `plugins`
--

CREATE TABLE IF NOT EXISTS `plugins` (
  `plugin_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `plugin_system_name` varchar(255) NOT NULL,
  `plugin_name` varchar(255) NOT NULL,
  `plugin_uri` varchar(120) DEFAULT NULL,
  `plugin_version` varchar(30) NOT NULL,
  `plugin_description` text,
  `plugin_author` varchar(120) DEFAULT NULL,
  `plugin_author_uri` varchar(120) DEFAULT NULL,
  `plugin_data` longtext,
  PRIMARY KEY (`plugin_id`),
  UNIQUE KEY `plugin_index` (`plugin_system_name`) USING BTREE
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `plugins`
--

INSERT INTO `plugins` (`plugin_id`, `plugin_system_name`, `plugin_name`, `plugin_uri`, `plugin_version`, `plugin_description`, `plugin_author`, `plugin_author_uri`, `plugin_data`) VALUES
(1, 'blacklist_number', 'Blacklist Number', 'http://azhari.harahap.us', '0.1', 'Autoremove incoming SMS from Blacklist number', 'Azhari Harahap', 'http://azhari.harahap.us', NULL),
(2, 'server_alert', 'Server Alert', 'http://azhari.harahap.us', '0.1', 'Send alert SMS when your server down', 'Azhari Harahap', 'http://azhari.harahap.us', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plugin_blacklist_number`
--

CREATE TABLE IF NOT EXISTS `plugin_blacklist_number` (
  `id_blacklist_number` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` varchar(15) NOT NULL,
  `reason` varchar(255) NOT NULL,
  PRIMARY KEY (`id_blacklist_number`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `plugin_server_alert`
--

CREATE TABLE IF NOT EXISTS `plugin_server_alert` (
  `id_server_alert` int(11) NOT NULL AUTO_INCREMENT,
  `alert_name` varchar(100) NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `port_number` int(5) NOT NULL,
  `timeout` int(4) NOT NULL DEFAULT '30',
  `phone_number` varchar(15) NOT NULL,
  `respond_message` varchar(135) NOT NULL,
  `status` enum('true','false') NOT NULL DEFAULT 'true',
  `release_code` varchar(8) NOT NULL,
  PRIMARY KEY (`id_server_alert`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;