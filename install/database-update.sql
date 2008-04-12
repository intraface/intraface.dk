
CREATE TABLE `project` (
  `id` int(11) NOT NULL auto_increment,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `intranet_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 ;

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
<<<<<<< .mine
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;


ALTER TABLE `newsletter_list` ADD `optin_link` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `newsletter_list` ADD `subscribe_subject` VARCHAR( 255 ) NOT NULL ;
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

ALTER TABLE `cms_template` ADD `for_page_type` INT NOT NULL AFTER `site_id` ;
UPDATE cms_template SET for_page_type = 7 ;

