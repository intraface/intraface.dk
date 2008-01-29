ALTER TABLE `invoice_payment` ADD `date_stated` DATE NOT NULL DEFAULT '0000-00-00' AFTER `amount` ,
ADD `voucher_id` INT NOT NULL DEFAULT '0' AFTER `date_stated` ;

ALTER TABLE `invoice_reminder` ADD `date_stated` DATE NOT NULL AFTER `date_cancelled` ,
ADD `voucher_id` INT NOT NULL AFTER `date_stated` ;

ALTER TABLE `procurement` CHANGE `total_price` `_old_total_price` DOUBLE( 11, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' ;
ALTER TABLE `procurement` CHANGE `total_price_items` `price_items` DOUBLE( 11, 2 ) UNSIGNED NOT NULL DEFAULT '0.00' ;
ALTER TABLE `procurement` ADD `price_shipment_etc` DOUBLE( 11, 2 ) UNSIGNED NOT NULL AFTER `price_items` ;

## notice that values are changed!!!
 UPDATE procurement SET `price_shipment_etc` = IF( `_old_total_price` - `price_items` - `vat` <0, 0, `_old_total_price` - `price_items` - `vat` )  ; 