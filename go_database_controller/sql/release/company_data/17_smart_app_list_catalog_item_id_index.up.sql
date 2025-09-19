use company_data;

ALTER TABLE `smart_app_list` ADD INDEX `catalog_item_id` (`catalog_item_id` ASC);
ALTER TABLE `smart_app_user_rel` ADD INDEX `smart_app_id.status` (`smart_app_id` ASC, `status` ASC)