-- --------------------------------------------------------

--
-- Table structure for table `user_filters`
--

CREATE TABLE IF NOT EXISTS `user_filters` (
  `id_filter` int(11) NOT NULL AUTO_INCREMENT,
  `id_user` int(11) NOT NULL,
  `from` varchar(15) NOT NULL,
  `has_the_words` varchar(50) NOT NULL,
  `id_folder` int(11) NOT NULL,
  PRIMARY KEY (`id_filter`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;