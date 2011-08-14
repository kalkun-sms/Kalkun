-- --------------------------------------------------------

--
-- Table structure for table `plugin_sms_member`
--

CREATE TABLE IF NOT EXISTS `plugin_sms_member` (
  `id_member` int(11) NOT NULL AUTO_INCREMENT,
  `phone_number` text NOT NULL,
  `reg_date` datetime NOT NULL,
  PRIMARY KEY (`id_member`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1;