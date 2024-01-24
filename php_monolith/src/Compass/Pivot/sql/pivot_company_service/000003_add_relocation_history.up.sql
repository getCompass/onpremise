CREATE TABLE IF NOT EXISTS `pivot_company_service`.`relocation_history` (
	`relocation_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'id задачи',
	`is_success` TINYINT(1) NOT NULL COMMENT 'флаг завершения процесса переезда',
	`created_at` INT(11) NOT NULL COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL COMMENT 'время изменения записи',
	`finished_at` INT(11) NOT NULL COMMENT 'время окончания процесса переезда',
	`company_id` BIGINT(20) NOT NULL COMMENT 'id компании',
	`source_domino_id` VARCHAR(64) NOT NULL COMMENT 'с какого домино переезжаем',
	`target_domino_id` VARCHAR(64) NOT NULL COMMENT 'на какое домино переезжаем',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`relocation_id`),
	INDEX `get_by_company_id` (`company_id` DESC, `created_at` DESC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'таблица истории релокации компании';