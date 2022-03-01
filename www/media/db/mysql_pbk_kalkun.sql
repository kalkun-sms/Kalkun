-- --------------------------------------------------------

--
-- Alter table structure for table `pbk`
--

ALTER TABLE `pbk` ADD `id_user` INT( 11 ) NOT NULL;
ALTER TABLE `pbk` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';


-- --------------------------------------------------------

--
-- Alter table structure for table `pbk_groups`
--

ALTER TABLE `pbk_groups` ADD `id_user` INT( 11 ) NOT NULL;
ALTER TABLE `pbk_groups` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';
