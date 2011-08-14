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