-- --------------------------------------------------------

--
-- Table structure for table `plugin_sms_to_xmpp`
--

CREATE TABLE IF NOT EXISTS `plugin_sms_to_xmpp` (
  `id_user` int(11) NOT NULL,
  `xmpp_host` varchar(100) NOT NULL,
  `xmpp_port` int(5) NOT NULL,
  `xmpp_username` varchar(50) NOT NULL,
  `xmpp_password` varchar(255) NOT NULL,
  `xmpp_server` varchar(100) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;
