USE `pivot_user_10m`;

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_1` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_2` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_3` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_4` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_5` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_6` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_7` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_8` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_9` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`user_company_push_token_10` (
	`token_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id компании',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	PRIMARY KEY (`token_hash`),
	INDEX `user_id` (`user_id` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся push токены пользователя';