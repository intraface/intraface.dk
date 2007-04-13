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

