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
