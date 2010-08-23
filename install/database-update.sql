ALTER TABLE accounting_year
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE accounting_account
ADD FOREIGN KEY (year_id) REFERENCES accounting_year(id);
ALTER TABLE accounting_post
ADD FOREIGN KEY (year_id) REFERENCES accounting_year(id);
ALTER TABLE accounting_vat_period
ADD FOREIGN KEY (year_id) REFERENCES accounting_year(id);
ALTER TABLE accounting_voucher
ADD FOREIGN KEY (year_id) REFERENCES accounting_year(id);
ALTER TABLE accounting_voucher_file
ADD FOREIGN KEY (voucher_id) REFERENCES accounting_voucher(id);
ALTER TABLE accounting_year_end
ADD FOREIGN KEY (year_id) REFERENCES accounting_year(id);

--
ALTER TABLE accounting_year_end_action
ADD FOREIGN KEY (year_id) REFERENCES accounting_year(id);
ALTER TABLE accounting_year_end_statement
ADD FOREIGN KEY (year_id) REFERENCES accounting_year(id);
--

ALTER TABLE basket
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

ALTER TABLE basket_details
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

ALTER TABLE dbquery_result
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

ALTER TABLE cms_site
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE cms_element
ADD FOREIGN KEY (section_id) REFERENCES cms_section(id);
ALTER TABLE cms_page
ADD FOREIGN KEY (site_id) REFERENCES cms_site(id);
#ALTER TABLE cms_parameter
#ADD FOREIGN KEY (site_id) REFERENCES cms_site(id);
ALTER TABLE cms_section
ADD FOREIGN KEY (site_id) REFERENCES cms_site(id);
ALTER TABLE cms_template
ADD FOREIGN KEY (site_id) REFERENCES cms_site(id);
ALTER TABLE cms_template_section
ADD FOREIGN KEY (site_id) REFERENCES cms_site(id);
ALTER TABLE cms_parameter
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);


ALTER TABLE comment
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

ALTER TABLE contact
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE `contact_message` CHANGE `contact_id` `contact_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE contact_message
ADD FOREIGN KEY (contact_id) REFERENCES contact(id);
ALTER TABLE `contact_person` CHANGE `contact_id` `contact_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE contact_person
ADD FOREIGN KEY (contact_id) REFERENCES contact(id);
ALTER TABLE `contact_reminder_single` CHANGE `contact_id` `contact_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE contact_reminder_single
ADD FOREIGN KEY (contact_id) REFERENCES contact(id);

ALTER TABLE `currency` CHANGE `id` `id` BIGINT( 20 ) UNSIGNED NOT NULL AUTO_INCREMENT ;
ALTER TABLE currency
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE `currency_exchangerate` CHANGE `currency_id` `currency_id` BIGINT( 20 ) UNSIGNED NOT NULL ;
ALTER TABLE currency_exchangerate
ADD FOREIGN KEY (currency_id) REFERENCES currency(id);

ALTER TABLE debtor
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE debtor_item
ADD FOREIGN KEY (debtor_id) REFERENCES debtor(id);
ALTER TABLE debtor_item
ADD FOREIGN KEY (product_id) REFERENCES product(id);

----

ALTER TABLE email
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE email_attachment
ADD FOREIGN KEY (email_id) REFERENCES email(id);

ALTER TABLE file_handler
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE filehandler_append_file
ADD FOREIGN KEY (file_handler_id) REFERENCES file_handler(id);
ALTER TABLE file_handler_instance
ADD FOREIGN KEY (file_handler_id) REFERENCES file_handler(id);
ALTER TABLE file_handler_instance_type
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

----

ALTER TABLE ilib_category
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE ilib_category_append
ADD FOREIGN KEY (category_id) REFERENCES ilib_category(id);

ALTER TABLE invoice_payment
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE invoice_reminder
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE invoice_reminder_item
ADD FOREIGN KEY (invoice_reminder_id) REFERENCES invoice_reminder(id);
ALTER TABLE invoice_reminder_unpaid_reminder
ADD FOREIGN KEY (invoice_reminder_id) REFERENCES invoice_reminder(id);

---

ALTER TABLE intranet_module_package
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE module_package_action
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE module_package_module
ADD FOREIGN KEY (module_package_id) REFERENCES module_package(id);

---

ALTER TABLE language
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);


ALTER TABLE module_sub_access
ADD FOREIGN KEY (module_id) REFERENCES module(id);

ALTER TABLE keyword
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

ALTER TABLE keyword_x_object
ADD FOREIGN KEY (keyword_id) REFERENCES keyword(id);

ALTER TABLE newsletter_list
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE newsletter_archieve
ADD FOREIGN KEY (list_id) REFERENCES newsletter_list(id);
ALTER TABLE newsletter_subscriber
ADD FOREIGN KEY (list_id) REFERENCES newsletter_list(id);
ALTER TABLE `newsletter_subscriber` CHANGE `contact_id` `contact_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE newsletter_subscriber
ADD FOREIGN KEY (contact_id) REFERENCES contact(id);

-----

ALTER TABLE onlinepayment
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE onlinepayment_settings
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

ALTER TABLE permission
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);

----

ALTER TABLE procurement
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE procurement_item
ADD FOREIGN KEY (procurement_id) REFERENCES procurement(id);

ALTER TABLE product
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE product_attribute
ADD FOREIGN KEY (attribute_group_id) REFERENCES product_attribute_group(id);
ALTER TABLE product_attribute_group
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE product_detail
ADD FOREIGN KEY (product_id) REFERENCES product(id);
ALTER TABLE product_related
ADD FOREIGN KEY (product_id) REFERENCES product(id);
ALTER TABLE product_variation
ADD FOREIGN KEY (product_id) REFERENCES product(id);
ALTER TABLE product_variation_detail
ADD FOREIGN KEY (product_variation_id) REFERENCES product_variation(id);
ALTER TABLE product_variation_x_attribute
ADD FOREIGN KEY (product_variation_id) REFERENCES product_variation(id);
ALTER TABLE product_variation_x_attribute
ADD FOREIGN KEY (product_attribute_id) REFERENCES product_attribute(id);

ALTER TABLE product_x_attribute_group
ADD FOREIGN KEY (product_id) REFERENCES product(id);

ALTER TABLE project
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE project_task
ADD FOREIGN KEY (project_id) REFERENCES project(id);

ALTER TABLE redirect
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE redirect_parameter
ADD FOREIGN KEY (redirect_id) REFERENCES redirect(id);
ALTER TABLE redirect_parameter_value
ADD FOREIGN KEY (redirect_id) REFERENCES redirect(id);

ALTER TABLE setting
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);


ALTER TABLE shop
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE shop_featuredproducts
ADD FOREIGN KEY (shop_id) REFERENCES shop(id);
ALTER TABLE shop_featuredproducts
ADD FOREIGN KEY (keyword_id) REFERENCES keyword(id);

ALTER TABLE shop_paymentmethods
ADD FOREIGN KEY (shop_id) REFERENCES shop(id);
ALTER TABLE webshop_basket_evaluation
ADD FOREIGN KEY (shop_id) REFERENCES shop(id);

ALTER TABLE stock_adaptation
ADD FOREIGN KEY (product_id) REFERENCES product(id);
ALTER TABLE stock_regulation
ADD FOREIGN KEY (product_id) REFERENCES product(id);


ALTER TABLE todo_list
ADD FOREIGN KEY (intranet_id) REFERENCES intranet(id);
ALTER TABLE todo_item
ADD FOREIGN KEY (todo_list_id) REFERENCES todo_list(id);
ALTER TABLE todo_contact
ADD FOREIGN KEY (list_id) REFERENCES todo_list(id);
ALTER TABLE `todo_contact` CHANGE `contact_id` `contact_id` INT( 11 ) UNSIGNED NOT NULL DEFAULT '0';
ALTER TABLE todo_contact
ADD FOREIGN KEY (contact_id) REFERENCES contact(id);


