USE `pivot_user_20m`;

ALTER TABLE `user_list_11` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());

CREATE TABLE IF NOT EXISTS `user_list_12` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_13` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_14` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_15` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_16` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_17` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_18` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_19` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

CREATE TABLE IF NOT EXISTS `user_list_20` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`npc_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип пользователя',
	`partner_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, который пригласил в проект и теперь получает партнерскую долю от оплаты плана',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания пользователя',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования пользователя',
	`full_name_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления имени',
	`country_code` VARCHAR(3) NOT NULL DEFAULT '' COMMENT 'код страны',
	`short_description` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'статус пользователя',
	`full_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'полное имя',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка пользователей';

ALTER TABLE `user_company_list_11` RENAME `company_list_11`;
ALTER TABLE `company_list_11` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `company_list_11` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());

CREATE TABLE IF NOT EXISTS `company_list_12` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_13` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_14` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_15` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_16` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_17` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_18` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_19` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

CREATE TABLE IF NOT EXISTS `company_list_20` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которой пользователь',
	`is_has_pin` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'имеет ли пинкод',
	`order` INT(1) NOT NULL DEFAULT 0 COMMENT 'на каком месте по порядку в компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп данные',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для списка компаний';

ALTER TABLE `user_company_lobby_list_1` RENAME `company_lobby_list_11`;
ALTER TABLE `user_company_lobby_list_2` RENAME `company_lobby_list_12`;
ALTER TABLE `user_company_lobby_list_3` RENAME `company_lobby_list_13`;
ALTER TABLE `user_company_lobby_list_4` RENAME `company_lobby_list_14`;
ALTER TABLE `user_company_lobby_list_5` RENAME `company_lobby_list_15`;
ALTER TABLE `user_company_lobby_list_6` RENAME `company_lobby_list_16`;
ALTER TABLE `user_company_lobby_list_7` RENAME `company_lobby_list_17`;
ALTER TABLE `user_company_lobby_list_8` RENAME `company_lobby_list_18`;
ALTER TABLE `user_company_lobby_list_9` RENAME `company_lobby_list_19`;
ALTER TABLE `user_company_lobby_list_10` RENAME `company_lobby_list_20`;

ALTER TABLE `user_company_dynamic_1` RENAME `company_inbox_11`;
ALTER TABLE `company_inbox_11` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_2` RENAME `company_inbox_12`;
ALTER TABLE `company_inbox_12` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_3` RENAME `company_inbox_13`;
ALTER TABLE `company_inbox_13` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_4` RENAME `company_inbox_14`;
ALTER TABLE `company_inbox_14` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_5` RENAME `company_inbox_15`;
ALTER TABLE `company_inbox_15` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_6` RENAME `company_inbox_16`;
ALTER TABLE `company_inbox_16` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_7` RENAME `company_inbox_17`;
ALTER TABLE `company_inbox_17` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_8` RENAME `company_inbox_18`;
ALTER TABLE `company_inbox_18` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_9` RENAME `company_inbox_19`;
ALTER TABLE `company_inbox_19` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
ALTER TABLE `user_company_dynamic_10` RENAME `company_inbox_20`;
ALTER TABLE `company_inbox_20` CHANGE COLUMN `company_id` `company_id` BIGINT(20) NOT NULL DEFAULT 0;
 
ALTER TABLE `user_company_push_token_1` RENAME `notification_company_push_token_11`;
ALTER TABLE `user_company_push_token_2` RENAME `notification_company_push_token_12`;
ALTER TABLE `user_company_push_token_3` RENAME `notification_company_push_token_13`;
ALTER TABLE `user_company_push_token_4` RENAME `notification_company_push_token_14`;
ALTER TABLE `user_company_push_token_5` RENAME `notification_company_push_token_15`;
ALTER TABLE `user_company_push_token_6` RENAME `notification_company_push_token_16`;
ALTER TABLE `user_company_push_token_7` RENAME `notification_company_push_token_17`;
ALTER TABLE `user_company_push_token_8` RENAME `notification_company_push_token_18`;
ALTER TABLE `user_company_push_token_9` RENAME `notification_company_push_token_19`;
ALTER TABLE `user_company_push_token_10` RENAME `notification_company_push_token_20`;

ALTER TABLE `user_notification_list_1` RENAME `notification_list_11`;
ALTER TABLE `notification_list_11` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_2` RENAME `notification_list_12`;
ALTER TABLE `notification_list_12` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_3` RENAME `notification_list_13`;
ALTER TABLE `notification_list_13` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_4` RENAME `notification_list_14`;
ALTER TABLE `notification_list_14` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_5` RENAME `notification_list_15`;
ALTER TABLE `notification_list_15` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_6` RENAME `notification_list_16`;
ALTER TABLE `notification_list_16` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_7` RENAME `notification_list_17`;
ALTER TABLE `notification_list_17` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_8` RENAME `notification_list_18`;
ALTER TABLE `notification_list_18` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_9` RENAME `notification_list_19`;
ALTER TABLE `notification_list_19` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `user_notification_list_10` RENAME `notification_list_20`;
ALTER TABLE `notification_list_20` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());

ALTER TABLE `device_token_1` RENAME `notification_token_11`;
ALTER TABLE `notification_token_11` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_2` RENAME `notification_token_12`;
ALTER TABLE `notification_token_12` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_3` RENAME `notification_token_13`;
ALTER TABLE `notification_token_13` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_4` RENAME `notification_token_14`;
ALTER TABLE `notification_token_14` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_5` RENAME `notification_token_15`;
ALTER TABLE `notification_token_15` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_6` RENAME `notification_token_16`;
ALTER TABLE `notification_token_16` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_7` RENAME `notification_token_17`;
ALTER TABLE `notification_token_17` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_8` RENAME `notification_token_18`;
ALTER TABLE `notification_token_18` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_9` RENAME `notification_token_19`;
ALTER TABLE `notification_token_19` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());
ALTER TABLE `device_token_10` RENAME `notification_token_20`;
ALTER TABLE `notification_token_20` CHANGE COLUMN `extra` `extra` JSON NOT NULL DEFAULT (JSON_ARRAY());

CREATE TABLE IF NOT EXISTS `user_last_call_11` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_12` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_13` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_14` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_15` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_16` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_17` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_18` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_19` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `user_last_call_20` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL,
	`call_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор последнего звонка',
	`is_finished` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - закончен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип звонка',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда звонок был создан',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COMMENT = 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';


CREATE TABLE IF NOT EXISTS `user_security_11` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_12` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_13` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_14` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_15` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_16` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_17` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_18` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_19` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `user_security_20` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения исходного номера телефона';

CREATE TABLE IF NOT EXISTS `mbti_selection_list` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'Id пользователя',
	`mbti_type` VARCHAR(10) NOT NULL DEFAULT '' COMMENT 'mbti_type пользователя',
	`text_type` VARCHAR(30) NOT NULL DEFAULT '' COMMENT 'тип текста для которого сделали выделение',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Дата обновления записи',
	`color_selection_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список выбранных цветов пользователем с позицией в тексте',
	PRIMARY KEY (`user_id`, `mbti_type`, `text_type`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для работы с диапазонами цветов пользователя';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_11` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_12` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_13` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_14` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_15` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_16` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_17` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_18` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_19` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';

CREATE TABLE IF NOT EXISTS `user_company_session_token_list_20` (
	`user_company_session_token` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`session_uniq` VARCHAR (255) NOT NULL DEFAULT 0 COMMENT 'сессия пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус токена',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания токена',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления токена',
	PRIMARY KEY (`user_company_session_token`),
	INDEX `session_uniq` (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения токенов';


CREATE TABLE IF NOT EXISTS `session_active_list_11` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_12` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_13` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_14` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_15` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_16` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_17` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_18` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_19` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

CREATE TABLE IF NOT EXISTS `session_active_list_20` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`ua_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'доп. данные',
	PRIMARY KEY (`session_uniq`),
	INDEX (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения активных сессий';

/* DROP TABLE IF EXISTS `announcement_cache_11`; */
/* DROP TABLE IF EXISTS `announcement_cache_12`; */
/* DROP TABLE IF EXISTS `announcement_cache_13`; */
/* DROP TABLE IF EXISTS `announcement_cache_14`; */
/* DROP TABLE IF EXISTS `announcement_cache_15`; */
/* DROP TABLE IF EXISTS `announcement_cache_16`; */
/* DROP TABLE IF EXISTS `announcement_cache_17`; */
/* DROP TABLE IF EXISTS `announcement_cache_18`; */
/* DROP TABLE IF EXISTS `announcement_cache_19`; */
/* DROP TABLE IF EXISTS `announcement_cache_20`; */
/* DROP TABLE IF EXISTS `user_invite_list_11`; */
