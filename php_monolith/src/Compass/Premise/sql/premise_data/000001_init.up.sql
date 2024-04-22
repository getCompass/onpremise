use `premise_data`;

CREATE TABLE IF NOT EXISTS `premise_data`.`premise_config` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ настройки',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`value` MEDIUMTEXT NOT NULL COMMENT 'значение конфига',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для конфига onpremise';