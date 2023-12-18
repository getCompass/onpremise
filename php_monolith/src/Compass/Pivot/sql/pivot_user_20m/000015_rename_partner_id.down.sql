USE `pivot_user_20m`;

-- переименовываем поля обратно
ALTER TABLE `user_list_11` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_12` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_13` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_14` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_15` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_16` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_17` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_18` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_19` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_20` CHANGE COLUMN invited_by_partner_id partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';