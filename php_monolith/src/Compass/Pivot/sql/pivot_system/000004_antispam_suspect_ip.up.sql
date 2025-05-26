use `pivot_system`;

CREATE TABLE IF NOT EXISTS `antispam_suspect_ip` (
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'ip адрес',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка до которой будет активна блокировка',
	`delayed_till` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда истечет время даваемое пользователю на ввод',
	PRIMARY KEY (`ip_address`),
	INDEX `created_at` (`created_at` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по ip';
