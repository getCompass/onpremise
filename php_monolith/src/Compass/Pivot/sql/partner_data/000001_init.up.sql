use `partner_data`;

CREATE TABLE IF NOT EXISTS `partner_data`.`invite_code_list` (
	`invite_code_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш сумма от кода',
	`invite_code` VARCHAR(255) NOT NULL  DEFAULT '' COMMENT 'код в чистом виде',
	`partner_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор партнера, которому принадлежит код',
	`discount` INT(11) NOT NULL DEFAULT 0 COMMENT 'процент скидки, которую предоставляет код',
	`can_reuse_after` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, после какого времени промокод будет доступен для закрепления новым партнером',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка выгорания записи',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	PRIMARY KEY (`invite_code_hash`),
        INDEX `invite_code` (`invite_code`) COMMENT 'индекс для получения записи по коду')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения кодов';
