USE `federation_system`;

CREATE TABLE IF NOT EXISTS `federation_system`.`antispam_ip` (
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'ip адрес',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ для блокировки',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`ip_address`, `key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по ip';