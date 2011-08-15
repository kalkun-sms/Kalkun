-- --------------------------------------------------------

--
-- Table structure for table `plugin_sms_to_email`
--

CREATE TABLE IF NOT EXISTS `plugin_sms_to_email` (
  `id_user` int(11) NOT NULL,
  `email_forward` ENUM('true', 'false') NOT NULL DEFAULT 'false',
  `email_id` VARCHAR(64) NOT NULL,
  UNIQUE(`id_user`)
) ENGINE=MyISAM;