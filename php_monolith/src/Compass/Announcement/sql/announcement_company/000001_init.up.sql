USE `announcement_company`;

CREATE TABLE IF NOT EXISTS `company_user_0`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_1`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_2`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_3`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_4`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_5`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_6`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_7`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_8`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';

CREATE TABLE IF NOT EXISTS `company_user_9`
(
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`company_id`, `user_id`),
	INDEX company_user_expires_at (`expires_at`),
	INDEX get_by_user_id (`user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи компаний и пользователей';