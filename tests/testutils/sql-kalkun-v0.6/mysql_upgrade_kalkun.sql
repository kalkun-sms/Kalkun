 
ALTER TABLE `user_settings` ADD `country_code` varchar(2) NOT NULL DEFAULT 'US';

-- --------------------------------------------------------

--
-- Table structure for table `user_forgot_password`
--

CREATE TABLE IF NOT EXISTS `user_forgot_password` (
  `id_user` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `valid_until` datetime NOT NULL,
  PRIMARY KEY (`id_user`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;