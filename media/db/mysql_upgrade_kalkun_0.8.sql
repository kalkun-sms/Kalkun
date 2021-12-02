-- --------------------------------------------------------

--
-- Table structure for table `ci_sessions`
-- see: https://codeigniter.com/userguide3/libraries/sessions.html#database-driver
--

CREATE TABLE IF NOT EXISTS `ci_sessions` (
  `id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
  `data` blob NOT NULL,
  PRIMARY KEY (`id`),
  KEY `ci_sessions_timestamp` (`timestamp`)
);


-- --------------------------------------------------------

--
-- Update password for "kalkun" user to "kalkun" (for version >= 0.8-dev)
-- if the password was still the default
update `user`
set
  password = '$2y$10$sIXe0JiaTIOsC7OOnox5t.deuJwZoawd5QKpQlSNfywziTDHpmmyy'
where password = 'f0af18413d1c9e0366d8d1273160f55d5efeddfe';


-- --------------------------------------------------------

--
-- Convert tables to utf8mb4
--
ALTER TABLE `kalkun` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `sms_used` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_folders` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_inbox` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_outbox` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_sentitems` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_settings` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_group` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_templates` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `b8_wordlist` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `plugins` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_forgot_password` CONVERT TO CHARACTER SET utf8mb4;
ALTER TABLE `user_filters` CONVERT TO CHARACTER SET utf8mb4;


-- --------------------------------------------------------

--
-- Convert tables to InnoDB Engine
--

ALTER TABLE `kalkun` ENGINE=InnoDB;
ALTER TABLE `sms_used` ENGINE=InnoDB;
ALTER TABLE `user` ENGINE=InnoDB;
ALTER TABLE `user_folders` ENGINE=InnoDB;
ALTER TABLE `user_inbox` ENGINE=InnoDB;
ALTER TABLE `user_outbox` ENGINE=InnoDB;
ALTER TABLE `user_sentitems` ENGINE=InnoDB;
ALTER TABLE `user_settings` ENGINE=InnoDB;
ALTER TABLE `user_group` ENGINE=InnoDB;
ALTER TABLE `user_templates` ENGINE=InnoDB;
ALTER TABLE `b8_wordlist` ENGINE=InnoDB;
ALTER TABLE `plugins` ENGINE=InnoDB;
ALTER TABLE `user_forgot_password` ENGINE=InnoDB;
ALTER TABLE `user_filters` ENGINE=InnoDB;
