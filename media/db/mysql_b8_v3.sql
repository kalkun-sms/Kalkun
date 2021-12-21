-- --------------------------------------------------------

--
-- Table structure for table `b8_wordlist` v3 (for b8 >= v0.6)
--

CREATE TABLE `b8_wordlist` (
  `token` varchar(190) character set utf8mb4 collate utf8mb4_bin NOT NULL,
  `count_ham` int unsigned default NULL,
  `count_spam` int unsigned default NULL,
  PRIMARY KEY (`token`)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

INSERT INTO `b8_wordlist` (`token`, `count_ham`) VALUES ('b8*dbversion', '3');

-- The one below is done in php, during upgrade process
-- INSERT INTO `b8_wordlist` (`token`, `count_ham`, `count_spam`) VALUES ('b8*texts', '0', '0');
