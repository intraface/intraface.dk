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
ALTER TABLE `contact_reminder_single` CHANGE `status` `status_key` INT( 11 ) NOT NULL ;

ALTER TABLE `debtor` ADD `internal_note` TEXT NOT NULL AFTER `message` ;

CREATE TABLE `webshop_basket_evaluation` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `running_index` int(11) NOT NULL,
  `evaluate_target_key` int(11) NOT NULL,
  `evaluate_method_key` int(11) NOT NULL,
  `evaluate_value` varchar(255) NOT NULL,
  `go_to_index_after` int(11) NOT NULL,
  `action_action_key` int(11) NOT NULL,
  `action_value` varchar(255) NOT NULL,
  `action_quantity` int(11) NOT NULL,
  `action_unit_key` int(11) NOT NULL,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  ;

ALTER TABLE `basket` ADD `basketevaluation_product` INT NOT NULL AFTER `date_changed` ;

ALTER TABLE `basket` ADD `text` TEXT NOT NULL ;

ALTER TABLE `accounting_account` CHANGE `primosaldo_debet` `primosaldo_debet` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
ALTER TABLE `accounting_account` CHANGE `primosaldo_credit` `primosaldo_credit` DOUBLE( 11, 2 ) NOT NULL DEFAULT '0.00'
