USE `pivot_user_20m`;

ALTER TABLE `user_list_11` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_12` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_13` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_14` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_15` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_16` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_17` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_18` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_19` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;
ALTER TABLE `user_list_20` ADD COLUMN `invited_by_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-пригласителя, который пригласил пользователя' AFTER `partner_id`;

ALTER TABLE `user_list_11` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_12` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_13` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_14` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_15` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_16` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_17` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_18` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_19` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;
ALTER TABLE `user_list_20` ADD COLUMN `last_active_day_start_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время начала дня последней активности пользователя' AFTER `invited_by_user_id`;