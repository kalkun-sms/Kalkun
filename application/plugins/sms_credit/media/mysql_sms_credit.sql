CREATE TABLE IF NOT EXISTS  `plugin_sms_credit` (
`id_user_credit` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`id_user` INT( 11 ) NOT NULL ,
`id_template_credit` INT( 11 ) NOT NULL ,
`valid_start` DATETIME NOT NULL ,
`valid_end` DATETIME NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE IF NOT EXISTS `plugin_sms_credit_template` (
`id_credit_template` INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`template_name` VARCHAR( 50 ) NOT NULL ,
`sms_numbers` INT( 11 ) NOT NULL
) ENGINE = MYISAM ;