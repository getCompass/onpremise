use company_data;

ALTER TABLE `userbot_list` ADD COLUMN `smart_app_name` VARCHAR(40) NOT NULL DEFAULT "" COMMENT 'уникальное имя smart app в рамках компании' AFTER `user_id`;
ALTER TABLE `userbot_list` ADD INDEX `smart_app_name` (`smart_app_name` ASC);
