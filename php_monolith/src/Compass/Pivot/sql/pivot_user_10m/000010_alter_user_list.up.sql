USE `pivot_user_10m`;

ALTER TABLE `user_list_1` ADD COLUMN `full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата смены имени пользователем в компанию' AFTER `updated_at`;
ALTER TABLE `user_list_1` DROP COLUMN `status`;