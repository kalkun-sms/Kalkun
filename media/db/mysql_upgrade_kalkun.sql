-- Kalkun upgrade from 0.3 to Higher
 
ALTER TABLE  `sms_used` CHANGE  `sms_count`  `out_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'
ALTER TABLE  `sms_used` ADD  `out_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'

ALTER TABLE `pbk` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';
ALTER TABLE `pbk_groups` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';

-- move and insert spam folder at location 6 
UPDATE `inbox` set id_folder = id_folder + 1 where id_folder > 5 
UPDATE `sentitems` set id_folder = id_folder + 1 where id_folder > 5
UPDATE `user_folders` set id_folder = id_folder + 1 where id_folder > 5
INSERT INTO `user_folders` (`id_folder`, `name`, `id_user`) VALUES (6, 'Spam', 0);
