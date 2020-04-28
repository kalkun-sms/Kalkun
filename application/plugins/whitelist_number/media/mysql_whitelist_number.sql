-- --------------------------------------------------------

--
-- Table structure for table `plugin_whitelist_number`
--

CREATE TABLE IF NOT EXISTS `plugin_whitelist_number` (
  `id_whitelist` int(5) NOT NULL AUTO_INCREMENT,
  `match` varchar(200) NOT NULL,
  PRIMARY KEY (`id_whitelist`)
);