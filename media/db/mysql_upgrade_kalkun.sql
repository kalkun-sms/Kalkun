-- Kalkun upgrade from 0.3 to Higher
 
ALTER TABLE  `sms_used` CHANGE  `sms_count`  `out_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'
ALTER TABLE  `sms_used` ADD  `out_sms_count` INT( 11 ) NOT NULL DEFAULT  '0'

ALTER TABLE `pbk` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';
ALTER TABLE `pbk_groups` ADD `is_public` enum('true','false') NOT NULL DEFAULT 'false';