use `pivot_system`;

CREATE TABLE IF NOT EXISTS `subnet_24_check_list` (
	`subnet_24` INT(11) UNSIGNED NOT NULL COMMENT 'первый ip из подсети /24, преобразованный через функцию ip2long()',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус проверки: 0 - нужна проверка; 1 - проверка выполнена',
	`checked_ip` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'ip по которому проверяли подсеть',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'время следующей работы крона',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` MEDIUMTEXT NOT NULL COMMENT 'дополнительные поля',
	PRIMARY KEY (`subnet_24`),
    INDEX `status.need_work` (`status` ASC, `need_work` ASC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COMMENT='таблица для крона проверки подсетей';

CREATE TABLE IF NOT EXISTS `subnet_24_result_list` (
	`subnet_24` INT(11) UNSIGNED NOT NULL COMMENT 'первый ip из подсети /24, преобразованный через функцию ip2long()',
	`is_mobile` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 если мобильный subnet',
	`is_proxy` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 если прокси subnet',
	`is_hosting` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 если хостинговый subnet',
	`country_code` VARCHAR(16) NOT NULL DEFAULT '' COMMENT 'код страны',
	`as` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя автономной системы',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	PRIMARY KEY (`subnet_24`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COMMENT='таблица с результатом проверки подсетей';
