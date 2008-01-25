ALTER TABLE `invoice_payment` ADD `date_stated` DATE NOT NULL DEFAULT '0000-00-00' AFTER `amount` ,
ADD `voucher_id` INT NOT NULL DEFAULT '0' AFTER `date_stated` ;

ALTER TABLE `invoice_reminder` ADD `date_stated` DATE NOT NULL AFTER `date_cancelled` ,
ADD `voucher_id` INT NOT NULL AFTER `date_stated` ;
