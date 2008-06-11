ALTER TABLE `basket` ADD `shop_id` INT( 11 ) NOT NULL ;
ALTER TABLE `basket_details` ADD `shop_id` INT( 11 ) NOT NULL ;
ALTER TABLE `shop_featuredproducts` ADD `shop_id` INT( 11 ) NOT NULL ;
ALTER TABLE `webshop_basket_evaluation` ADD `shop_id` INT( 11 ) NOT NULL ;


CREATE TABLE `project_task` (
  `id` int(11) NOT NULL auto_increment,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `project_id` int(11) NOT NULL,
  `intranet_id` int(11) NOT NULL,
  `item` text NOT NULL,
  `user_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


CREATE TABLE `shop` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `identifier` varchar(255) NOT NULL,
  `receipt` text NOT NULL,
  `confirmation` text NOT NULL,
  `description` text NOT NULL,
  `show_online` int(1) NOT NULL,
  `intranet_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1;

ALTER TABLE `debtor` ADD `identifier_key` VARCHAR( 255 ) NOT NULL AFTER `user_id` ;
UPDATE `debtor` SET `identifier_key` = MD5( CONCAT( `id` , `intranet_id` , `date_created` , `number` , `description` ) ) WHERE `identifier_key` = '' ;
 
 