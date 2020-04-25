-- --------------------------------------------------------

--
-- Table structure for table `plugin_stop_manager`
--

CREATE TABLE IF NOT EXISTS `plugin_stop_manager` (
    `id_stop_manager` int(5) NOT NULL AUTO_INCREMENT,
    `destination_number` varchar(20) NOT NULL,
    `stop_type` varchar(50) NOT NULL,
    `stop_message` varchar(2000) NOT NULL,
    `reg_date` datetime NOT NULL,
    PRIMARY KEY (`id_stop_manager`)
) DEFAULT CHARSET=utf8;
