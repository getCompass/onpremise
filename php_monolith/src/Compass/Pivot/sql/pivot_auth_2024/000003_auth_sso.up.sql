USE `pivot_auth_2024`;

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_1` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_2` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_3` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_4` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_5` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_6` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_7` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_8` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_9` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_10` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_11` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2024`.`auth_sso_list_12` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
        UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';