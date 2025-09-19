USE `pivot_user_20m`;

DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_11`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_12`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_13`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_14`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_15`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_16`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_17`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_18`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_19`;
DROP TABLE IF EXISTS `pivot_user_20m`.`user_activity_list_20`;

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_1` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_2` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_3` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_4` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_5` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_6` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_7` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_8` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_9` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';

CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_activity_list_10` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`last_ws_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последней активности подключенного ws',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список активности пользователей';