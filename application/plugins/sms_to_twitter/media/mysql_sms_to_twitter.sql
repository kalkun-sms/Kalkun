-- --------------------------------------------------------

--
-- Table structure for table `plugin_sms_to_twitter`
--

CREATE TABLE IF NOT EXISTS `plugin_sms_to_twitter` (
  `id_user` int(11) NOT NULL,
  `access_token` VARCHAR(255) NOT NULL,
  `access_token_secret` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM;