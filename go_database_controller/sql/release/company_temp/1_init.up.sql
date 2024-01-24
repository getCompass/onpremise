/* @formatter:off */
use company_temp;
CREATE TABLE IF NOT EXISTS `preview_redirect_checker_log` (
	`log_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор лога',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус лога success/error',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество посещений ссылок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания лога',
	`total_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, затраченное на посещение всех ссылок, ms',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор отправителя',
	`original_link` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ссылка, отправленая пользователем',
	`body` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительная информация',
	PRIMARY KEY (`log_id`),
	INDEX `status` (`status` ASC),
	INDEX `original_link` (`original_link` ASC)
	) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для логирования крона редирект чекера';

CREATE TABLE IF NOT EXISTS `preview_parser_log` (
	`log_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор лога',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус лога success/error',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество посещений ссылок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания лога',
	`total_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, затраченное на посещение всех ссылок, ms',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор отправителя',
	`original_link` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ссылка, отправленая пользователем',
	`html` LONGTEXT NOT NULL COMMENT 'html сайта',
	`body` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительная информация',
	PRIMARY KEY (`log_id`),
	INDEX `status` (`status` ASC),
	INDEX `created_at` (`created_at` ASC),
	INDEX `original_link` (`original_link` ASC)
	) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для логирования крона парсера';