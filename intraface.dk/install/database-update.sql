ALTER TABLE `email` ADD `contact_person_id` INT NOT NULL AFTER `contact_id` ;
ALTER TABLE `email` ADD `bcc_to_user` INT NOT NULL AFTER `user_id` ;

ALTER TABLE `basket` ADD `product_detail_id` INT NOT NULL AFTER `product_id` ;

CREATE TABLE `intranet_module_package` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `module_package_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `order_debtor_id` int(11) NOT NULL,
  `status_key` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

CREATE TABLE `module_package` (
  `id` int(11) NOT NULL auto_increment,
  `module_package_group_id` int(11) NOT NULL,
  `module_package_plan_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

CREATE TABLE `module_package_action` (
  `id` int(11) NOT NULL auto_increment,
  `intranet_id` int(11) NOT NULL,
  `order_debtor_id` int(11) NOT NULL,
  `date_created` datetime NOT NULL,
  `action` text NOT NULL,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

CREATE TABLE `module_package_group` (
  `id` int(11) NOT NULL auto_increment,
  `group_name` varchar(255) NOT NULL,
  `sorting_index` int(11) NOT NULL,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

CREATE TABLE `module_package_module` (
  `id` int(11) NOT NULL auto_increment,
  `module_package_id` int(11) NOT NULL,
  `module` varchar(255) NOT NULL,
  `limiter` varchar(255) NOT NULL,
  `active` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;

CREATE TABLE `module_package_plan` (
  `id` int(11) NOT NULL auto_increment,
  `plan` varchar(255) NOT NULL,
  `plan_index` int(11) NOT NULL,
  `active` int(11) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM ;
