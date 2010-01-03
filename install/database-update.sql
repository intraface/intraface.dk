## Remeber trailing semicolons on every command ##

CREATE TABLE shop_dicount_campaign (id BIGINT AUTO_INCREMENT, name VARCHAR(255) NOT NULL, voucher_code_prefix VARCHAR(255) NOT NULL, intranet_id BIGINT, deleted_at DATETIME, PRIMARY KEY(id));
CREATE TABLE shop_dicount_campaign_voucher (id BIGINT AUTO_INCREMENT, shop_discount_campaign_id INT DEFAULT '0' NOT NULL, code VARCHAR(255) NOT NULL, quantity BIGINT DEFAULT '0' NOT NULL, date_created DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL, date_expiry DATETIME DEFAULT '0000-00-00 00:00:00' NOT NULL, used_on_debtor_id BIGINT DEFAULT '0' NOT NULL, created_from_debtor_id BIGINT DEFAULT '0' NOT NULL, intranet_id BIGINT, deleted_at DATETIME, INDEX shop_discount_campaign_id_idx (shop_discount_campaign_id), PRIMARY KEY(id));
ALTER TABLE shop_dicount_campaign_voucher ADD CONSTRAINT sssi FOREIGN KEY (shop_discount_campaign_id) REFERENCES shop_dicount_campaign(id);
