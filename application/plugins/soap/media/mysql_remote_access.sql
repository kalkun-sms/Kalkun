

-- --------------------------------------------------------

--
-- Table structure for table `plugin_server_alert`
--
DROP TABLE IF EXISTS `plugin_remote_access`;
CREATE TABLE `plugin_remote_access` (
  `id_remote_access` int(11) NOT NULL auto_increment,
  `access_name` varchar(100) NOT NULL,
  `ip_address` varchar(20) NOT NULL,
  `token` varchar(135) NOT NULL,
  `status` enum('true','false') NOT NULL default 'true',
  PRIMARY KEY  (`id_remote_access`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
