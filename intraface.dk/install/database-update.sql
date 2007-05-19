CREATE TABLE `contact_reminder_single` (
`id` INT NOT NULL AUTO_INCREMENT ,
`intranet_id` INT NOT NULL ,
`contact_id` INT NOT NULL ,
`reminder_date` DATETIME NOT NULL ,
`status` INT NOT NULL ,
`date_created` DATETIME NOT NULL ,
`date_changed` DATETIME NOT NULL ,
`date_viewed` DATETIME NOT NULL ,
`date_cancelled` DATETIME NOT NULL ,
`subject` VARCHAR( 255 ) NOT NULL ,
`description` TEXT NOT NULL ,
`active` INT DEFAULT '1' NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;

ALTER TABLE `contact_reminder_single` ADD `created_by_user_id` INT NOT NULL AFTER `contact_id` ;

ALTER TABLE `debtor` ADD `internal_note` TEXT NOT NULL AFTER `message` ;


CREATE TABLE `webshop_basket_filter` (
`id` INT NOT NULL AUTO_INCREMENT ,
`intranet_id` INT NOT NULL ,
`filter_index` INT NOT NULL ,
`evaluate_key` INT NOT NULL ,
`evaluate_method` INT NOT NULL ,
`evaluate_value` VARCHAR( 255 ) NOT NULL ,
`exit_after` INT NOT NULL ,
`action_key` INT NOT NULL ,
`action_value` VARCHAR( 255 ) NOT NULL ,
`action_amount` INT NOT NULL ,
`action_unit` INT NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;
