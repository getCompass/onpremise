USE `pivot_user_10m`;

ALTER TABLE `user_company_dynamic_1` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_1` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_2` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_2` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_3` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_3` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_4` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_4` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_5` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_5` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_6` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_6` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_7` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_7` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_8` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_8` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_9` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_9` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';

ALTER TABLE `user_company_dynamic_10` DROP COLUMN `inbox_unread_count`;
ALTER TABLE `user_company_dynamic_10` CHANGE `messages_unread_count_alias` `total_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений у пользователя';