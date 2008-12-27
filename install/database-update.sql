
# Already added to running database
ALTER TABLE `dbquery_result` ADD INDEX `search` ( `id` , `intranet_id` , `session_id` , `name` )  
ALTER TABLE `dbquery_result` DROP INDEX `intranet_id`  
ALTER TABLE `file_handler` ADD INDEX `simple_find` ( `id` , `intranet_id` )  


# New tables
CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `type_key` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

ALTER TABLE `shop` ADD `language_key` INT( 11 ) NOT NULL ;

ALTER TABLE `product_detail` ADD `before_price` FLOAT( 11, 2 ) NOT NULL AFTER `price` ;
