use `jitsi_data`;

CREATE TABLE IF NOT EXISTS `jitsi_data`.`conference_list` (
	`conference_id` VARCHAR(255) NOT NULL COMMENT 'ID конференции',
	`space_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пространства, в рамках которого была создана конференция',
	`status` INT NOT NULL DEFAULT 0 COMMENT 'Статус конференции',
	`is_private` TINYINT NOT NULL DEFAULT 0 COMMENT 'Флаг, является ли конференция приватной, то есть доступна только участникам пространства. Гости к такой конференции присоединиться не могут',
	`is_lobby` TINYINT NOT NULL DEFAULT 0 COMMENT 'Флаг, включено ли лобби в конференции',
	`creator_user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пользователя-создателя конференции',
	`password` VARCHAR(60) NOT NULL DEFAULT '' COMMENT 'Пароль конференции',
	`jitsi_instance_domain` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Домен инстанса Jitsi, который обслуживает данную конференцию',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	`data` MEDIUMTEXT NOT NULL COMMENT 'Доп. данные по конференции – версионная JSON структура. На старте предлагаю ничего не хранить здесь, задел на будущее',
	PRIMARY KEY (`conference_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения всех конференций';

CREATE TABLE IF NOT EXISTS `jitsi_data`.`conference_member_list` (
	`conference_id` VARCHAR(255) NOT NULL COMMENT 'ID конференции',
	`member_type` INT NOT NULL DEFAULT 0 COMMENT 'Тип участника',
	`member_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ID участника. В зависимости от типа участника сюда записываются разные значения',
	`is_moderator` TINYINT NOT NULL DEFAULT 0 COMMENT 'Флаг, является ли участник модератором в конференции',
	`status` INT NOT NULL DEFAULT 0 COMMENT 'Статус участника в конференции',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP адрес участника в конференции',
	`user_agent` VARCHAR(512) NOT NULL DEFAULT '' COMMENT 'User agent приложения/браузера участника, с которым он вступил в конференцию. Для пользователя Compass здесь будет user-agent приложения вместе с платформой и версией; Для гостя из браузера здесь будет user-agent его браузера',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	`data` MEDIUMTEXT NOT NULL COMMENT 'Доп. данные по участнику конференции – версионная JSON структура. На старте предлагаю ничего не хранить здесь, задел на будущее',
	PRIMARY KEY (`conference_id`,`member_type`,`member_id`),
	INDEX `conference_id.created_at` (`conference_id`,`created_at` DESC) COMMENT 'для выборки всех участников из таблицы для конкретной конференции по убыванию (от новых записей к старым)',
	INDEX `member_type.member_id` (`member_type`, `member_id`) COMMENT 'для выборки всех конференций, к которым присоединялся конкретный пользователь или гость')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения участников конференции';

CREATE TABLE IF NOT EXISTS `jitsi_data`.`user_active_conference_rel` (
	`user_id` BIGINT NOT NULL COMMENT 'ID пользователя',
	`active_conference_id` VARCHAR(255) NOT NULL COMMENT 'ID активной конференции',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	PRIMARY KEY (`user_id`),
	INDEX `active_conference_id` (`active_conference_id`) COMMENT 'для выборки всех участников, у которых активна конкретная конференция')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения активной конференции пользователя';
