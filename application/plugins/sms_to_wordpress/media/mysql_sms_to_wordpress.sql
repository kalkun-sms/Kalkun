-- --------------------------------------------------------

--
-- Table structure for table `plugin_sms_to_wordpress`
--

CREATE TABLE IF NOT EXISTS `plugin_sms_to_wordpress` (
  `id_user` int(11) NOT NULL,
  `wp_username` VARCHAR(50) NOT NULL,
  `wp_password` VARCHAR(255) NOT NULL,
  `wp_url` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM;