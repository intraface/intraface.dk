# Allready updated on server:

ALTER TABLE `ilib_category` ADD `active` INT( 1 ) NOT NULL DEFAULT '1' ;
ALTER TABLE `newsletter_subscriber` ADD `date_unsubscribe` DATE NOT NULL ;

# New

CREATE TABLE `product_detail_translation` (
`id` INT NOT NULL ,
`lang` CHAR( 2 ) NOT NULL ,
`name` VARCHAR( 255 ) NOT NULL ,
`description` TEXT NOT NULL ,
PRIMARY KEY ( `id`, `lang` )
) ENGINE = MYISAM ;

ALTER TABLE `contact` ADD `code` VARCHAR( 255 ) NOT NULL 
ALTER TABLE `contact` ADD `username` VARCHAR( 255 ) NOT NULL 




