USE `pivot_user_10m`;

CREATE TABLE IF NOT EXISTS `user_list_1` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус пользователя',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_company_list_1` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус (в компании, уволен и тд)',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	PRIMARY KEY (`user_id`, `company_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `user_invite_list_1` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, которого приглашают',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую приглашают',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'номер телефона (хэш)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания приглашения',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления приглашения',
	PRIMARY KEY (`invite_id`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка приглашений в компанию';

CREATE TABLE IF NOT EXISTS `announcement_cache_1` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_2` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_3` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_4` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_5` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_6` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_7` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_8` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_9` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';

CREATE TABLE IF NOT EXISTS `announcement_cache_10` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'уникальный идентификатор сессии пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`platform` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'платформа пользователя',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'точка времени, после которой нужно снова показать анонс пользователю',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время обновления записи',
	PRIMARY KEY (`session_uniq`),
	INDEX `get_by_user_id_and_platform` (`user_id` ASC, `platform` ASC)  COMMENT  'индекс для выборки по пользователю и его платформе')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с отправкой анонсов пользователям';