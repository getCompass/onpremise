USE `pivot_data`;

CREATE TABLE IF NOT EXISTS `pivot_data`.`checkpoint_phone_number_list` (
	`list_type` TINYINT(4) NOT NULL COMMENT 'тип списка',
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'phone_number_hash, который в списке',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, до которого действительно',
	PRIMARY KEY (`list_type`, `phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения чекпоинтов по номеру телефона';

CREATE TABLE IF NOT EXISTS `pivot_data`.`checkpoint_company_list` (
	`list_type` TINYINT(4) NOT NULL COMMENT 'тип списка',
	`company_id` BIGINT(20) NOT NULL COMMENT 'company_id компании, которая в списке',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, до которого действительно',
	PRIMARY KEY (`list_type`, `company_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для чекпоинтов компаний';

CREATE TABLE IF NOT EXISTS `pivot_data`.`pivot_config` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ',
	`value` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица со значениями различных конфигов и констант';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_0` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_0` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_1` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_1` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_2` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_2` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_3` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_3` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_4` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_4` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_5` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_5` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_6` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_6` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_7` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_7` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_8` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_8` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_9` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_9` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_a` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_a` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_b` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_b` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_c` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_c` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_d` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_d` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_e` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_e` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_list_f` (
	`device_id` VARCHAR(36) NOT NULL COMMENT 'идентификатор девайса',
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`device_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения устройств и токенов для них';

CREATE TABLE IF NOT EXISTS `pivot_data`.`device_token_voip_list_f` (
	`token_hash` VARCHAR(40) NOT NULL COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся хэш токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_free_list` (
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания компании',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования компании',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`company_id`),
	INDEX `created_at` (`created_at` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица-очередь со свободными компаниями';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_0` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_1` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_2` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_3` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_4` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_5` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_6` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_7` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_8` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_9` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_a` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_b` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_c` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_d` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_e` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_invite_link_rel_f` (
	`invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус_алиас ссылки-инвайта (берет начало в php_company в таблице с ссылками-инвайтами)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_link_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'хранит данные по каждой ссылке, каждой компании, для того чтобы правильно редиректить пользователя в нужную компанию';

CREATE TABLE IF NOT EXISTS `pivot_data`.`company_task_queue` (
	`company_task_id` INT(11) NOT NULL AUTO_INCREMENT,
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании куда пойдет исполнение',
	`type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип задачи',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус задачи',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'время запуска крона',
	`iteration_count` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'количество итераций',
	`error_count` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'количество ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`done_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время потраченое на работу',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'системные данные',
	PRIMARY KEY (`company_task_id`),
	INDEX `company_id` (`company_id`),
	INDEX `type_status_need_work` (`type` ASC, `status` ASC, `need_work` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с задачами для компании';