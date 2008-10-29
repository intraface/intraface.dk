ALTER TABLE `shop` ADD `default_currency_id` INT NOT NULL AFTER `terms_of_trade_url` ;

ALTER TABLE `newsletter_subscriber` ADD `resend_optin_email_count` INT NOT NULL AFTER `ip_optin` ;