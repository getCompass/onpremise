USE `pivot_user_20m`;

ALTER TABLE `user_list_11` ADD COLUMN `full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата смены имени пользователем в компанию' AFTER `updated_at`;
ALTER TABLE `user_list_11` DROP COLUMN `status`;