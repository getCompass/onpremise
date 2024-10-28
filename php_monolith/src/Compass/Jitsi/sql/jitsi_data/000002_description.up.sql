use `jitsi_data`;


CREATE TABLE IF NOT EXISTS `jitsi_data`.`permanent_conference_list` (
	`conference_id` VARCHAR(255) NOT NULL COMMENT 'ID конференции',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства, в рамках которого была создана конференция',
	`is_deleted` TINYINT NOT NULL DEFAULT 0 COMMENT 'Флаг, является ли конференция приватной, то есть доступна только участникам пространства. Гости к такой конференции присоединиться не могут',
	`creator_user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пользователя-создателя конференции',
	`conference_url_custom_name` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'Кастомная ссылка пользователя',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	PRIMARY KEY (`conference_id`),
	INDEX `get_by_user` (`space_id`, `is_deleted`, `creator_user_id`),
	INDEX `get_by_unique` (`space_id`, `is_deleted`, `creator_user_id`, `conference_url_custom_name`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения постоянных конференций';

ALTER TABLE `jitsi_data`.`conference_list` ADD COLUMN `conference_url_custom_name` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'кастомная часть ссылки' AFTER `creator_user_id`;
ALTER TABLE `jitsi_data`.`conference_list` ADD COLUMN `description` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'название конференции' AFTER `conference_url_custom_name`;
