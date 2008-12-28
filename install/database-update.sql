# New tables
CREATE TABLE IF NOT EXISTS `language` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `type_key` int(11) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `intranet_id` (`intranet_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 ;

ALTER TABLE `shop` ADD `language_key` INT( 11 ) NOT NULL ;
ALTER TABLE `language` ADD `type_key` INT( 11 ) NOT NULL ;

CREATE TABLE IF NOT EXISTS `onlinepayment_settings` (
  `id` int(11) NOT NULL,
  `intranet_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `onlinepayment_settings_translation` (
  `id` int(11) NOT NULL,
  `email` text,
  `lang` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

ALTER TABLE `onlinepayment_settings_translation` ADD `subject` VARCHAR( 255 ) NOT NULL ;
 ALTER TABLE `onlinepayment_settings_translation` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT
 ALTER TABLE `onlinepayment_settings` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT  

ALTER TABLE `product_detail` ADD `before_price` FLOAT( 11, 2 ) NOT NULL AFTER `price` ;
