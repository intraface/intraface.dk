INSERT INTO `intranet` ( `id` , `name` , `identifier` , `private_key` , `public_key` , `date_changed`) VALUES ( '1', 'Welcome to Intraface', 'intraface', 'privatekeyshouldbereplaced', 'publickeyshouldbereplaced', NOW());
INSERT INTO `user` ( `id` , `email` , `password`) VALUES ( '1', 'start@intraface.dk', MD5( 'startup' ));
INSERT INTO `module` ( `id` , `name` , `menu_label` , `show_menu` , `active`) VALUES ( '1', 'intranetmaintenance', 'intranetmaintenance', '1', '1');
INSERT INTO `permission` ( `id` , `intranet_id` , `user_id` , `module_id` , `module_sub_access_id` ) VALUES ('1', '1', '0', '1', '0');
INSERT INTO `permission` ( `id` , `intranet_id` , `user_id` , `module_id` , `module_sub_access_id` ) VALUES ('2', '1', '1', '0', '0');
INSERT INTO `permission` ( `id` , `intranet_id` , `user_id` , `module_id` , `module_sub_access_id` ) VALUES ('3', '1', '1', '1', '0');
INSERT INTO `core_translation_langs` (`id`, `name`, `meta`, `error_text`, `encoding`) VALUES ('dk', 'dansk', 'some meta info', 'ikke tilg�ngelig', 'iso-8859-1'), ('uk', 'english', 'some meta info', 'not available', 'iso-8859-1');
