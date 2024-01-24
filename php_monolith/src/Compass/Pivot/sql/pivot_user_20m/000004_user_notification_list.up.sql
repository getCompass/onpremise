USE `pivot_user_20m`;

CREATE TABLE IF NOT EXISTS `user_notification_list_1` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_2` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_3` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_4` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_5` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_6` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_7` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_8` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_9` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';

CREATE TABLE IF NOT EXISTS `user_notification_list_10` (
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор пользователя',
	`snoozed_until` INT(11) NOT NULL DEFAULT '0' COMMENT 'до какого момента времени отключены уведомления',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'время изменения записи',
	`device_list` JSON NOT NULL COMMENT 'список устройств пользователя',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для пользователя',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица, в которой хранятся данные устройств пользователя и состояние уведомлений';
