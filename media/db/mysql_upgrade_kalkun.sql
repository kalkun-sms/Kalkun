-- Kalkun upgrade from 0.3 to Higher
 
-- --------------------------------------------------------

--
-- Table structure for table `kalkun`
--

CREATE TABLE IF NOT EXISTS `kalkun` (
  `version` text NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


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

ALTER TABLE  `sms_used` CHANGE  `sms_count`  `out_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'
ALTER TABLE  `sms_used` ADD  `out_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'