use `federation_system`;

CREATE TABLE IF NOT EXISTS `federation_system`.`datastore` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица с системными значениями для фреймворка\nнужна для работы крона general.php, а также по необходимости в нее могут писать другие модули';
