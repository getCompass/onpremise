USE `announcement_user_20m`;

CREATE TABLE IF NOT EXISTS `user_announcement_0`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_1`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_2`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
	)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_3`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_4`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_5`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_6`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_7`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_8`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_announcement_9`
(
	`announcement_id`     BIGINT(11) NOT NULL COMMENT 'Id анонса',
	`user_id`             BIGINT(20) NOT NULL COMMENT 'Id пользователя', /* Добавить bigint */
	`is_read`             TINYINT(1) NOT NULL COMMENT 'Прочитан ли запрос',
	`created_at`          INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at`          INT(11)    NOT NULL COMMENT 'Время редактирования',
	`next_resend_at`      INT(11)    NOT NULL COMMENT 'Время следующей отправки', /* Добавить ключ на next_resend_at и resend_attempted_at */
	`resend_attempted_at` INT(11)    NOT NULL COMMENT 'Время последней отправки',
	`extra`               JSON       NOT NULL COMMENT 'Доп. данные',

	PRIMARY key (`announcement_id`, `user_id`),
	INDEX user_announcement_is_read (`user_id`, `is_read`),
	INDEX user_announcement_need_resend (`resend_attempted_at`, `next_resend_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и анонсов';

CREATE TABLE IF NOT EXISTS `user_company_0`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_1`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_2`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_3`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_4`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_5`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_6`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_7`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_8`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';

CREATE TABLE IF NOT EXISTS `user_company_9`
(
	`user_id`    BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`company_id` INT(11)    NOT NULL COMMENT 'Id компании',
	`expires_at` INT(11)    NOT NULL COMMENT 'Время истечения',
	`created_at` INT(11)    NOT NULL COMMENT 'Время создания',
	`updated_at` INT(11)    NOT NULL COMMENT 'Время редактирования',

	PRIMARY key (`user_id`, `company_id`),
	INDEX user_company_expires_at (`expires_at`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для связи пользователей и компаний';