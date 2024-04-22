use `premise_system`;

CREATE TABLE IF NOT EXISTS `premise_system`.`antispam_ip` (
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'ip адрес',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ для блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и ip',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`ip_address`, `key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по ip';

CREATE TABLE IF NOT EXISTS `premise_system`.`antispam_user` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и user_id',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`user_id`, `key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по user_id';

CREATE TABLE IF NOT EXISTS `premise_system`.`datastore` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица с системными значениями для фреймворка\nнужна для работы крона general.php, а также по необходимости в нее могут писать другие модули';