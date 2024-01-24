USE `pivot_company_10m`;

CREATE TABLE IF NOT EXISTS `company_list_1` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_2` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_3` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_4` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_5` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_6` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_7` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_8` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_9` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_list_10` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена компания 1/0',
	`status` INT(1) NOT NULL DEFAULT 0 COMMENT 'статус компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время удаления компании',
	`avatar_color_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id цвета аватара',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя-создателя записи о компании',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил создателя в проект и теперь получает партнерскую долю от оплаты плана',
	`name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя компании',
	`url` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'url адрес компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`),
	INDEX `status` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с данными о компании';

CREATE TABLE IF NOT EXISTS `company_user_list_1` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_2` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_3` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_4` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_5` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_6` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_7` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_8` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_9` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';

CREATE TABLE IF NOT EXISTS `company_user_list_10` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`company_id`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей в компании';
