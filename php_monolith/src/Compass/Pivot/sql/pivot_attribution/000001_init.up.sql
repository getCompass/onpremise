USE `pivot_attribution`;

CREATE TABLE IF NOT EXISTS `pivot_attribution`.`landing_visit_log` (
	`visit_id` VARCHAR(36) NOT NULL COMMENT 'Уникальный ID посещения',
	`guest_id` VARCHAR(36) NOT NULL COMMENT 'Уникальный ID гостя',
	`link` VARCHAR(1024) NOT NULL COMMENT 'Ссылка, которую посетил пользователь',
	`utm_tag` VARCHAR(1024) NOT NULL COMMENT 'UTM тэг рекламной кампании',
	`source_id` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'source ID рекламной кампании',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP адрес',
	`platform` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Платформа посетителя',
	`platform_os` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'ОС платформы посетителя',
	`timezone_utc_offset` INT NOT NULL DEFAULT '0' COMMENT 'Сдвиг часового пояса относительно UTC в секундах',
	`screen_avail_width` INT NOT NULL DEFAULT '0' COMMENT 'Допустимая ширина экрана',
	`screen_avail_height` INT NOT NULL DEFAULT '0' COMMENT 'Допустимая высота экрана',
	`visited_at` INT NOT NULL DEFAULT '0' COMMENT 'Временная метка посещения страницы',
	`created_at` INT NOT NULL DEFAULT '0' COMMENT 'Временная метка создания записи',
	PRIMARY KEY (`visit_id`),
        INDEX `visited_at` (`visited_at`) COMMENT 'индекс для выборки по времени посещения')
	ENGINE = InnoDB
	COMMENT 'Таблица для хранения уникальных посещений и параметров цифровых подписей этих посещений';

CREATE TABLE IF NOT EXISTS `pivot_attribution`.`user_app_registration_log` (
	`user_id` BIGINT NOT NULL COMMENT 'ID пользователя',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP адрес',
	`platform` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'Платформа пользователя',
	`platform_os` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'ОС платформы пользователя',
	`timezone_utc_offset` INT NOT NULL DEFAULT '0' COMMENT 'Сдвиг часового пояса относительно UTC в секундах',
	`screen_avail_width` INT NOT NULL DEFAULT '0' COMMENT 'Допустимая ширина экрана',
	`screen_avail_height` INT NOT NULL DEFAULT '0' COMMENT 'Допустимая высота экрана',
	`registered_at` INT NOT NULL DEFAULT '0' COMMENT 'Временная метка регистрации пользователя',
	`created_at` INT NOT NULL DEFAULT '0' COMMENT 'Временная метка создания записи',
	PRIMARY KEY (`user_id`),
        INDEX `registered_at` (`registered_at`) COMMENT 'индекс для выборки по времени регистрации')
	ENGINE = InnoDB
	COMMENT 'Таблица для хранения параметров цифровой подписи регистрирующихся пользоавтелей';

CREATE TABLE IF NOT EXISTS `pivot_attribution`.`user_campaign_rel` (
	`user_id` BIGINT NOT NULL COMMENT 'ID пользователя',
	`visit_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'UUID посещения',
	`utm_tag` VARCHAR(512) NOT NULL DEFAULT '' COMMENT 'UTM тэг рекламной кампании',
	`source_id` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'source ID рекламной кампании',
	`link` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT 'ссылка посещения',
	`is_direct_reg` TINYINT NOT NULL DEFAULT '0' COMMENT 'Флаг 0/1 – зарегистрирован ли пользователь по рекламной кампании',
	`created_at` INT NOT NULL DEFAULT '0' COMMENT '',
	PRIMARY KEY (`user_id`),
        INDEX `visit_id` (`visit_id`) COMMENT 'Для выборки записей по UUID посещения')
	ENGINE = InnoDB
	COMMENT 'Таблица для хранения связей user_id <-> visit_id для определения страницы, поситив которую пользователь зарегистрировался в приложении';