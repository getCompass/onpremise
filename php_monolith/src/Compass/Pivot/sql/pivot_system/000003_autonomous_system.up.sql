use `pivot_system`;

CREATE TABLE IF NOT EXISTS `autonomous_system` (
	`id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'инкрементальный идентификатор',
	`ip_range_start` BIGINT(20) NOT NULL COMMENT 'начало диапазона адресов',
	`ip_range_end` BIGINT(20) NOT NULL COMMENT 'конец диапазона адресов',
	`code` INT(11) NOT NULL DEFAULT 0 COMMENT 'код автономной системы',
	`country_code` VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'код страны, где зарегистрирована автономная система',
	`name` VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'имя автономной системы',
	PRIMARY KEY (`id`),
    INDEX `get_in_range` (`ip_range_start` ASC, `ip_range_end` ASC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COMMENT='таблица для хранения данных автономных систем';
