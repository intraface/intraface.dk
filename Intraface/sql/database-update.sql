ALTER TABLE `email` ADD `contact_person_id` INT NOT NULL AFTER `contact_id` ;

ALTER TABLE `email` ADD `bcc_to_user` INT NOT NULL AFTER `user_id` ;
