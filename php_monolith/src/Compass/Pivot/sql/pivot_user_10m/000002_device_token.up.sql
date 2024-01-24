USE `pivot_user_10m`;

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_1` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_2` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_3` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_4` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_5` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_6` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_7` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_8` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_9` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';

CREATE TABLE IF NOT EXISTS `pivot_user_10m`.`device_token_10` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'До какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'последнее обновление',
	`token_list` JSON NOT NULL COMMENT 'массив с токенами пользователя, максимум 30',
	`extra` JSON NOT NULL COMMENT 'дополнительное поле',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для хранения токенов push уведомлений';