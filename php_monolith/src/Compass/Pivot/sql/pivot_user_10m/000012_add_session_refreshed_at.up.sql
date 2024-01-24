USE `pivot_user_10m`;

ALTER TABLE `session_active_list_1` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_2` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_3` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_4` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_5` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_6` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_7` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_8` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_9` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;
ALTER TABLE `session_active_list_10` ADD COLUMN `refreshed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления жизни сессии' AFTER `login_at`;