USE `pivot_phone`;

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_add_story` (
	`add_phone_story_id` BIGINT(20) AUTO_INCREMENT NOT NULL COMMENT 'id процесса',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус смены номера',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап смены номера',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда смена номера истечет',
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'сессия, к которой смена привязана',
	PRIMARY KEY (`add_phone_story_id`),
	INDEX `get_unused` (`expires_at`, `status`) COMMENT 'получаем неактивные')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории смен номеров телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_add_via_sms_story` (
	`add_phone_story_id` BIGINT(20) NOT NULL COMMENT 'id процесса',
	`phone_number` VARCHAR(45) NOT NULL COMMENT 'номер телефона',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'завершено ли подтверждение номера',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап процесса',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправок смс',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда можно переотправить',
	`sms_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'id смски',
	`sms_code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш смс кода',
	PRIMARY KEY (`add_phone_story_id`, `phone_number`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории подтверждения номеров для смены номера';