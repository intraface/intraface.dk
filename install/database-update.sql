ALTER TABLE `shop` ADD `default_currency_id` INT NOT NULL AFTER `terms_of_trade_url` ;

 CREATE TABLE `intraface`.`language` (
`id` INT NOT NULL AUTO_INCREMENT ,
`name` VARCHAR( 255 ) NOT NULL ,
`intranet_id` INT NOT NULL,

`identifier` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM 
ALTER TABLE `shop` ADD `default_currency_id` INT NOT NULL AFTER `terms_of_trade_url` ;

ALTER TABLE `newsletter_subscriber` ADD `resend_optin_email_count` INT NOT NULL AFTER `ip_optin` ;