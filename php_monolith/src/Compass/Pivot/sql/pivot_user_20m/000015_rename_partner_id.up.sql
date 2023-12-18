USE `pivot_user_20m`;

-- переименовываем поля
ALTER TABLE `user_list_11` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_12` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_13` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_14` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_15` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_16` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_17` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_18` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_19` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';
ALTER TABLE `user_list_20` CHANGE COLUMN partner_id invited_by_partner_id BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект';

