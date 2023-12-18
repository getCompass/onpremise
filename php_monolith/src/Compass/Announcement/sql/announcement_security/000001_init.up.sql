USE `announcement_security`;

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_0`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_1`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_2`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_3`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_4`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_5`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
	)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_6`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_7`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_8`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';

CREATE TABLE IF NOT EXISTS `announcement_security`.`token_user_9`
(
	`user_id`           INT(11)      NOT NULL COMMENT 'Id пользователя',
	`bound_session_key` VARCHAR(255) NOT NULL COMMENT 'Ключ сессии',
	`created_at`        INT(11)      NOT NULL COMMENT 'Время создания',
	`updated_at`        INT(11)      NOT NULL COMMENT 'Время редактирования',
	`expires_at`        INT(11)      NOT NULL COMMENT 'Время истечения',
	`token`             VARCHAR(255) NOT NULL COMMENT 'Токен',

	PRIMARY KEY (`user_id`, `bound_session_key`),
	INDEX get_expired (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для токенов пользователей';