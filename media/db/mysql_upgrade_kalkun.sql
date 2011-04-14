-- Kalkun upgrade from 0.2.9 to 0.2.10
 
ALTER TABLE `user` ADD `email_id` varchar(64) NOT NULL;

ALTER TABLE `user_settings` ADD `email_forward` ENUM( 'true', 'false' ) NOT NULL DEFAULT 'false';


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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;


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
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;


--
-- Table structure for table `plugin`
--

CREATE TABLE IF NOT EXISTS `plugin` (
  `id_plugin` int(11) NOT NULL auto_increment,
  `plugin_name` varchar(50) NOT NULL,
  `plugin_status` enum('true','false') NOT NULL,
  PRIMARY KEY  (`id_plugin`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;
