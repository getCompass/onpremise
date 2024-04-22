use `premise_user`;

CREATE TABLE IF NOT EXISTS `premise_user`.`user_list` (
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`npc_type_alias` INT NOT NULL  DEFAULT 0 COMMENT 'алиас типа пользователя',
	`space_status` INT NOT NULL DEFAULT 0 COMMENT 'статус участника в командах',
	`has_premise_permissions` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'есть ли права у пользователя',
	`premise_permissions` INT NOT NULL DEFAULT 0 COMMENT 'маска прав сервера',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`external_sso_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор SSO',
	`external_other1_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор внешнего сервиса #1',
	`external_other2_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор внешнего сервиса #2',
	`external_data` MEDIUMTEXT NOT NULL COMMENT 'информация о внешних сервисах',
	`extra` MEDIUMTEXT NOT NULL COMMENT 'дополнительные данные',
	PRIMARY KEY (`user_id`),
        INDEX `npc_type_alias.space_status` (`npc_type_alias`, `space_status`) COMMENT 'индекс для получения счетчиков по статусу в команде',
	INDEX `npc_type_alias.has_premise_permissions.created_at` (`npc_type_alias`, `has_premise_permissions`, `created_at`) COMMENT 'индекс для получения пользователей с правами',
	INDEX `external_sso_id` (`external_sso_id`) COMMENT 'индекс для SSO',
	INDEX `external_other1_id` (`external_other1_id`) COMMENT 'индекс для внешего сервиса #1',
	INDEX `external_other2_id` (`external_other2_id`) COMMENT 'индекс для внешего сервиса #2')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для пользователей onpremise';

CREATE TABLE IF NOT EXISTS `premise_user`.`space_list` (
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`space_id` BIGINT NOT NULL COMMENT 'id команды',
	`role_alias` INT NOT NULL DEFAULT 0 COMMENT 'алиас роли в команде',
	`permissions_alias` INT NOT NULL DEFAULT 0 COMMENT 'алиас прав в команде',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` MEDIUMTEXT NOT NULL COMMENT 'дополнительные данные',
	PRIMARY KEY (`user_id`, `space_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'связующая таблица для пользователей и команд onpremise';

CREATE TABLE IF NOT EXISTS `premise_user`.`space_counter` (
	`key` VARCHAR(40) NOT NULL COMMENT 'ключ счетчика',
	`count` INT NOT NULL  DEFAULT 0 COMMENT 'значение счетчика',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'счетчики пользователей в команде';
