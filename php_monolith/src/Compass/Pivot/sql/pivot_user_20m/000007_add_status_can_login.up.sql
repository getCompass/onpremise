USE `pivot_user_20m`;

ALTER TABLE `user_company_list_11` ADD COLUMN `status_alias` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'статус польвателя в компании';
ALTER TABLE `user_company_list_11` ADD COLUMN `can_login_alias` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'может ли пользователь логиниться в компанию';