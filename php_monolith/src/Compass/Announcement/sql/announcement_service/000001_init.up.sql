USE `announcement_service`;

CREATE TABLE IF NOT EXISTS `antispam_ip`
(
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'ip адрес',
	`key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ для блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'отправлена ли статистика для этого ключа и ip',
	`count` INT(11) NOT NULL DEFAULT '0' COMMENT 'количество попыток',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'время когда блокировка станет неактуальной',

	PRIMARY KEY (`ip_address`, `key`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для блокировок по ip';

CREATE TABLE IF NOT EXISTS `antispam_user`
(
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'отправлена ли статистика для этого ключа и user_id',
	`count` INT(11) NOT NULL DEFAULT '0' COMMENT 'количество попыток',
	`expire` INT(11) NOT NULL DEFAULT '0' COMMENT 'время когда блокировка станет неактуальной',

	PRIMARY KEY (`user_id`, `key`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для блокировок по user_id';

CREATE TABLE IF NOT EXISTS `phphooker_queue`
(
	`task_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'уникальный идентификатор задачи',
	`task_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип задачи определяемый константой',
	`error_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'количество попыток исполнения задачи кроном',
	`need_work` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка когда необходимо выполнить задачу',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка создания создания задачи',
	`params` JSON NOT NULL COMMENT 'параметры для исполнения задачи',

	PRIMARY KEY (`task_id`),
	INDEX `need_work` (`need_work` ASC)  COMMENT 'индекс для получения задач кроном'
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица с очередью задач на исполнение phphooker';

CREATE TABLE IF NOT EXISTS `datastore` (
	`key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ',
	`extra` JSON NOT NULL COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`key`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица с системными значениями для фреймворка\nнужна для работы крона general.php, а также по необходимости в нее могут писать другие модули';
