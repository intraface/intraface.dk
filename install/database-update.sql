
CREATE TABLE `product_attribute_group` (
`id` INT NOT NULL ,
`intranet_id` INT NOT NULL ,
`name` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;

 ALTER TABLE `product_attribute_group` CHANGE `id` `id` INT( 11 ) NOT NULL AUTO_INCREMENT ;
 
 ALTER TABLE `product_attribute_group` ADD `deleted` BOOL NOT NULL ;


 CREATE TABLE `product_attribute` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`intranet_id` INT NOT NULL ,
`attribute_group_id` INT NOT NULL ,
`name` VARCHAR( 255 ) NOT NULL ,
`position` INT NOT NULL ,
`deleted` TINYINT( 1 ) NOT NULL
) ENGINE = MYISAM ;
 
ALTER TABLE `product` ADD `has_variation` TINYINT( 1 ) NOT NULL DEFAULT '0' AFTER `do_show` ; 

CREATE TABLE `product_x_attribute_group` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`intranet_id` INT NOT NULL ,
`product_id` INT NOT NULL ,
`product_attribute_group_id` INT NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE `product_variation_x_attribute` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`intranet_id` INT NOT NULL ,
`product_variation_id` INT NOT NULL ,
`product_attribute_id` INT NOT NULL
) ENGINE = MYISAM ;

CREATE TABLE `product_variation` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`intranet_id` INT NOT NULL ,
`product_id` INT NOT NULL
) ENGINE = MYISAM ;

ALTER TABLE `product_variation` ADD `deleted` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `product_variation_x_attribute` ADD `attribute_number` INT NOT NULL DEFAULT '0' ;

 CREATE TABLE `product_variation_detail` (
`id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`intranet_id` INT NOT NULL ,
`date_created` DATETIME NOT NULL ,
`product_variation_id` INT NOT NULL ,
`price_difference` INT NOT NULL ,
`weight_difference` INT NOT NULL
) ENGINE = MYISAM ; 

ALTER TABLE `product_variation` ADD `number` INT NOT NULL AFTER `product_id` ;

ALTER TABLE `debtor_item` ADD `product_variation_id` INT NOT NULL AFTER `product_detail_id` ,
ADD `product_variation_detail_id` INT NOT NULL AFTER `product_variation_id` ;

ALTER TABLE `procurement_item` ADD `product_variation_id` INT NOT NULL AFTER `product_detail_id` ,
ADD `product_variation_detail_id` INT NOT NULL AFTER `product_variation_id` ;

ALTER TABLE `stock_adaptation` ADD `product_variation_id` INT NOT NULL AFTER `product_id` ;

ALTER TABLE `stock_regulation` ADD `product_variation_id` INT NOT NULL AFTER `product_id` ;

ALTER TABLE `basket` ADD `product_variation_id` INT NOT NULL DEFAULT '0' AFTER `product_detail_id` ;

## 11/7 2008 Sune

CREATE TABLE `ilib_category` (
`id` int(11) NOT NULL auto_increment,
`intranet_id` int(11) NOT NULL,
`belong_to` int(11) NOT NULL,
`belong_to_id` int(11) NOT NULL,
`parent_id` int(11) NOT NULL,
`name` varchar(255) NOT NULL,
`identifier` varchar(255) NOT NULL,
 PRIMARY KEY  (`id`) );
 
CREATE TABLE IF NOT EXISTS `ilib_category_append` (
`id` int(11) NOT NULL auto_increment,
`intranet_id` int(11) NOT NULL,
`object_id` int(11) NOT NULL,
`category_id` int(11) NOT NULL,
PRIMARY KEY  (`id`));

