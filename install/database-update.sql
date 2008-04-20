
CREATE TABLE `project` (
  `id` int(11) NOT NULL auto_increment,
  `date_created` datetime NOT NULL,
  `date_updated` datetime NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `intranet_id` int(11) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 ;

ALTER TABLE `cms_template` ADD `for_page_type` INT NOT NULL AFTER `site_id` ;
UPDATE cms_template SET for_page_type = 7 ;

ALTER TABLE `newsletter_list` ADD `optin_link` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `newsletter_list` ADD `subscribe_subject` VARCHAR( 255 ) NOT NULL ;

ALTER TABLE `redirect` ADD `cancel_url` VARCHAR( 255 ) NOT NULL ;