
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
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 AUTO_INCREMENT=21 ;

ALTER TABLE `webshop_basket_evaluation` ADD `shop_id` INT( 11 ) NOT NULL ;
ALTER TABLE `shop_featuredproducts` ADD `shop_id` INT( 11 ) NOT NULL ;