CREATE TABLE `file_handler_instance_type` (
`id` INT NOT NULL AUTO_INCREMENT ,
`intranet_id` INT NOT NULL ,
`name` VARCHAR( 255 ) NOT NULL ,
`max_height` INT NOT NULL ,
`max_width` INT NOT NULL ,
PRIMARY KEY ( `id` )
) TYPE = MYISAM ;

ALTER TABLE `file_handler_instance` CHANGE `type` `type_key` INT( 11 ) NOT NULL DEFAULT '0';

ALTER TABLE `file_handler_instance_type` ADD `type_key` INT NOT NULL AFTER `name` ;

ALTER TABLE `file_handler_instance_type` ADD `active` INT DEFAULT '1' NOT NULL ;

ALTER TABLE `file_handler_instance_type` ADD `resize_type_key` INT NOT NULL AFTER `max_width` ;

ALTER TABLE `dbquery_result` CHANGE `weblogin_session_id` `session_id` VARCHAR( 255 ) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL ;

ALTER TABLE `redirect` CHANGE `user_id` `session_id` VARCHAR( 255 ) NOT NULL ;

 ALTER TABLE `procurement` CHANGE `_old_state_account_id` `state_account_id` INT( 11 ) NOT NULL DEFAULT '0'