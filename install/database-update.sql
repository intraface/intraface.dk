 CREATE TABLE `intraface`.`shop_paymentmethods` (
`id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
`intranet_id` INT( 11 ) NOT NULL ,
`shop_id` INT( 11 ) NOT NULL ,
`paymentmethod_key` INT( 11 ) NOT NULL ,
`text` VARCHAR( 255 ) NOT NULL ,
PRIMARY KEY ( `id` )
) ENGINE = MYISAM ;

ALTER TABLE `product_variation_x_attribute` ADD INDEX `idx_intranet` ( `intranet_id` );
ALTER TABLE `product_variation_x_attribute` ADD INDEX `product_attribute_id` ( `product_attribute_id` );
ALTER TABLE `product_variation_x_attribute` ADD INDEX `idx_product_variation_attribute` ( `product_variation_id` , `attribute_number` );
ALTER TABLE `product_variation_detail` ADD INDEX ( `intranet_id` );
ALTER TABLE `product_variation_detail` ADD INDEX `product_variation_id` ( `product_variation_id` );
ALTER TABLE `product_x_attribute_group` ADD INDEX ( `intranet_id` );
ALTER TABLE `product_attribute` ADD INDEX ( `attribute_group_id` );     
ALTER TABLE `product_attribute` ADD INDEX ( `position` );
ALTER TABLE `product_attribute` ADD INDEX ( `intranet_id` , `deleted` );
ALTER TABLE `product_variation` ADD INDEX ( `intranet_id` , `product_id` ); 

ALTER TABLE `shop` ADD `confirmation_greeting` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `shop` ADD `confirmation_subject` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `shop` ADD `confirmation_add_contact_url` TINYINT(4) NOT NULL ;
ALTER TABLE `shop` ADD INDEX ( `intranet_id` )  ;
ALTER TABLE `shop_featuredproducts` ADD INDEX ( `keyword_id` )  ;
ALTER TABLE `shop_featuredproducts` ADD INDEX `intranet_id` ( `intranet_id` , `shop_id` );
    
ALTER TABLE `stock_adaptation` ADD INDEX ( `product_variation_id` )  ;
ALTER TABLE `stock_regulation` ADD INDEX ( `product_variation_id` );   
ALTER TABLE `webshop_basket_evaluation` ADD INDEX ( `intranet_id` , `shop_id` );
ALTER TABLE `debtor_item` ADD INDEX ( `product_variation_id` , `product_variation_detail_id` )  ;
ALTER TABLE `basket` ADD INDEX ( `product_detail_id` , `product_variation_id` )  ;
ALTER TABLE `basket` DROP INDEX `intranet_id` ADD INDEX `intranet_id` ( `intranet_id` , `product_id` , `shop_id` ) ;
ALTER TABLE `basket_details` ADD INDEX ( `intranet_id` , `shop_id` )  ;
ALTER TABLE `basket_details` ADD INDEX ( `order_id` )  ;
ALTER TABLE `basket_details` ADD INDEX ( `session_id` )  ;
ALTER TABLE `email` ADD INDEX ( `belong_to_id` )  ;
         
ALTER TABLE `file_handler_instance_type` ADD INDEX ( `intranet_id` )  ;
ALTER TABLE `ilib_category` ADD INDEX ( `intranet_id` , `belong_to` , `belong_to_id` )  ;
ALTER TABLE `ilib_category` ADD INDEX ( `parent_id` )  ;
ALTER TABLE `ilib_category_append` ADD INDEX ( `intranet_id` , `object_id` , `category_id` )  ;  
ALTER TABLE `intranet_module_package` ADD INDEX ( `intranet_id` , `module_package_id` )  ;
ALTER TABLE `intranet_module_package` ADD INDEX ( `order_debtor_id` )  ;
ALTER TABLE `invoice_reminder` ADD INDEX ( `intranet_id` )  ;
ALTER TABLE `invoice_reminder` ADD INDEX ( `contact_id` , `contact_address_id` )  ;
ALTER TABLE `invoice_reminder` ADD INDEX ( `invoice_id` )  ;
ALTER TABLE `invoice_reminder_item` ADD INDEX ( `intranet_id` , `invoice_reminder_id` , `invoice_id` )  ;
ALTER TABLE `invoice_reminder_unpaid_reminder` ADD INDEX ( `intranet_id` , `invoice_reminder_id` , `unpaid_invoice_reminder_id` )  ;
ALTER TABLE `kernel_log` ADD INDEX ( `intranet_id` )  ;
ALTER TABLE `module_package` ADD INDEX ( `module_package_group_id` , `module_package_plan_id` , `product_id` )  ;
ALTER TABLE `module_package_action` ADD INDEX ( `intranet_id` , `order_debtor_id` )  ;
ALTER TABLE `module_package_group` ADD INDEX ( `sorting_index` )  ;
ALTER TABLE `module_package_module` ADD INDEX ( `module_package_id` )  ;
ALTER TABLE `module_package_plan` ADD INDEX ( `plan_index` )  ;
ALTER TABLE `newsletter_archieve` ADD INDEX ( `list_id` , `intranet_id` )  ;
ALTER TABLE `newsletter_list` ADD INDEX ( `intranet_id` )  ;
ALTER TABLE `product_attribute_group` ADD INDEX ( `intranet_id` )  ;
ALTER TABLE `redirect` ADD INDEX `session_id` ( `session_id` ) 
ALTER TABLE `redirect_parameter` ADD INDEX ( `intranet_id` , `redirect_id` )  ;
ALTER TABLE `redirect_parameter_value` ADD INDEX ( `intranet_id` , `redirect_id` , `redirect_parameter_id` )  ;
