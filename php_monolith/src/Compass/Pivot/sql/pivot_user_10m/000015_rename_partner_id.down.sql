USE `pivot_user_10m`;

-- переименовываем поля обратно
ALTER TABLE `user_list_1` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_2` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_3` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_4` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_5` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_6` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_7` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_8` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_9` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_10` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';