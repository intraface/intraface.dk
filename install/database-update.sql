
ALTER TABLE `module_package_action` CHANGE `order_debtor_id` `order_debtor_identifier` VARCHAR( 255 ) NOT NULL DEFAULT '0';

ALTER TABLE `currency` ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL;
UPDATE `currency` SET deleted_at = NOW() WHERE deleted = 1;
ALTER TABLE `currency` CHANGE `deleted` `_old_deleted` INT( 1 ) NOT NULL ;

ALTER TABLE `product_attribute` ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL;
UPDATE `product_attribute` SET deleted_at = NOW() WHERE deleted = 1;
ALTER TABLE `product_attribute` CHANGE `deleted` `_old_deleted` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `product_attribute_group` ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL;
UPDATE `product_attribute_group` SET deleted_at = NOW() WHERE deleted = 1;
ALTER TABLE `product_attribute_group` CHANGE `deleted` `_old_deleted` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `product_variation` ADD `deleted_at` TIMESTAMP NULL DEFAULT NULL;
UPDATE `product_variation` SET deleted_at = NOW() WHERE deleted = 1;
ALTER TABLE `product_variation` CHANGE `deleted` `_old_deleted` TINYINT( 1 ) NOT NULL DEFAULT '0';

ALTER TABLE `module_package_action` ADD `identifier` VARCHAR( 255 ) NOT NULL DEFAULT '' AFTER `intranet_id` ;

ALTER TABLE `module_package_action` ADD UNIQUE ( `identifier` );