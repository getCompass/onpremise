USE `pivot_user_10m`;

ALTER TABLE `user_list_1` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_2` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_3` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_4` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_5` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_6` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_7` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_8` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_9` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_10` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;

ALTER TABLE `user_list_1` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_2` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_3` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_4` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_5` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_6` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_7` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_8` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_9` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_10` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;