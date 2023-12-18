USE `pivot_company_10m`;

CREATE TABLE IF NOT EXISTS `company_tier_observe` (
	`company_id` BIGINT(20) NOT NULL COMMENT 'id компании',
	`current_domino_tier` INT(11) NOT NULL DEFAULT 0 COMMENT 'ранг текущего домино компании',
	`expected_domino_tier` INT(11) NOT NULL DEFAULT 0 COMMENT 'ранг домино, на который перевозим компанию. 0 если никуда не нужно перевозить',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда необходимо прообзервить компанию',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`company_id`),
	INDEX `need_work` (`need_work` ASC),
	INDEX `expected_domino_tier` (`expected_domino_tier` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с компаниями, по которой обзервится ранг компании';