use `ldap_data`;

CREATE TABLE IF NOT EXISTS `ldap_data`.`mail_user_rel` (
	`uid` VARCHAR(255) NOT NULL COMMENT 'uid пользователя в LDAP',
	`mail_source` TINYINT NOT NULL DEFAULT 0 COMMENT 'Источник привязки почты (1 - LDAP, 2 - вручную)',
	`mail` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	`created_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	PRIMARY KEY (`uid`),
	UNIQUE INDEX `mail_UNIQUE` (`mail`) COMMENT 'почта уникальна для каждого пользователя')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'связующая таблица между пользователями и почтой';

CREATE TABLE IF NOT EXISTS `ldap_data`.`mail_confirm_story` (
	`mail_confirm_story_id` BIGINT  NOT NULL AUTO_INCREMENT COMMENT 'id процесса',
	`status` TINYINT NOT NULL DEFAULT 0 COMMENT 'статус процесса подтверждения почты',
	`stage` TINYINT NOT NULL DEFAULT 0 COMMENT 'этап процесса подтверждения',
	`created_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	`expires_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'время истекания попытки входа',
	`ldap_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'токен, для которого подтверждается вход',
	`uid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор польщзовтаеля в LDAP',
	PRIMARY KEY (`mail_confirm_story_id`),
	INDEX `expires_at.status` (`expires_at`, `status`) COMMENT 'неиспользованные попытки входа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'история подтверждений почты';

CREATE TABLE IF NOT EXISTS `ldap_data`.`mail_confirm_via_code_story` (
	`id` BIGINT  NOT NULL AUTO_INCREMENT COMMENT 'id процесса',
	`status` TINYINT NOT NULL DEFAULT 0 COMMENT 'статус подтверждения почты',
	`resend_count` INT NOT NULL DEFAULT 0 COMMENT 'количество переотправок проверочного кода',
	`error_count` INT NOT NULL DEFAULT 0 COMMENT 'количество ошибок ввода кода',
	`created_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	`next_resend_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'когда доступна следующая переотправка кода',
	`mail_confirm_story_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'идентификатор процесса входа',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'id отправленного сообщения',
	`code_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хеш отправленного кода',
	`mail` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'почта, на которую отправили код',
	PRIMARY KEY (`id`),
	INDEX `mail_confirm_story_id.id` (`mail_confirm_story_id`, `id`) COMMENT 'последняя попытка отправки кода для процесса входа')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'история отправленных кодов на почту';