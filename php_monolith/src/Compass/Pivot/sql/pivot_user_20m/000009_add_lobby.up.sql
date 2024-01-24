USE `pivot_user_20m`;

ALTER TABLE `user_company_list_11` DROP COLUMN `status_alias`;
ALTER TABLE `user_company_list_11` DROP COLUMN `can_login_alias`;
ALTER TABLE `user_company_list_11` ADD COLUMN `entry_id` INT(11) NOT NULL DEFAULT '0' COMMENT 'идентификатор с которым пользователь попал в компанию' AFTER `order`;
ALTER TABLE `user_company_list_11` ADD COLUMN `extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra пользователя' AFTER `updated_at`;

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_1` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_2` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_3` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_4` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_5` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_6` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_7` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_8` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_9` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

CREATE TABLE IF NOT EXISTS `user_company_lobby_list_10` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании, в которую пользователь еще не попал',
	`order` INT(11) NOT NULL DEFAULT 0 COMMENT 'сортировка, поле синхронизировано с таблицей user_company_list',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус пользователя относительно компании',
	`entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор последнего входа в компанию, или пытается попасть в компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле для дополнительных данных',
	PRIMARY KEY (`user_id`, `company_id`),
	INDEX `user_id_and_order` (`user_id`, `order` DESC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'предбанник, хранит пользователей которые еще не попали в компанию, или были удалены из нее';

ALTER TABLE `user_company_list_11` DROP COLUMN `status`;
DROP INDEX `company_id_status` ON `user_company_list_11`;
CREATE INDEX `user_id_and_order` ON `user_company_list_11` (`user_id`, `order` DESC);