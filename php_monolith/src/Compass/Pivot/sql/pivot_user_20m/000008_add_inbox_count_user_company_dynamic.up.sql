USE `pivot_user_20m`;

ALTER TABLE `user_company_dynamic_1` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_1`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_2` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_2`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_3` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_3`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_4` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_4`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_5` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_5`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_6` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_6`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_7` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_7`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_8` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_8`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_9` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_9`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;

ALTER TABLE `user_company_dynamic_10` CHANGE `total_unread_count_alias` `messages_unread_count_alias` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя';
ALTER TABLE `user_company_dynamic_10`
	ADD COLUMN `inbox_unread_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'общее количество непрочитанных чатов у пользователя' AFTER `messages_unread_count_alias`;