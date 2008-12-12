
# Already added to running database
ALTER TABLE `dbquery_result` ADD INDEX `search` ( `id` , `intranet_id` , `session_id` , `name` )  
ALTER TABLE `dbquery_result` DROP INDEX `intranet_id`  
ALTER TABLE `file_handler` ADD INDEX `simple_find` ( `id` , `intranet_id` )  