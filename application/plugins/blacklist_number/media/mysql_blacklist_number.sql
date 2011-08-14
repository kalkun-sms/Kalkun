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