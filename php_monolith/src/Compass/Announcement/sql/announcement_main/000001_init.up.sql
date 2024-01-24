USE `announcement_main`;

CREATE TABLE IF NOT EXISTS `announcement`
(
	`announcement_id`       BIGINT(11) auto_increment NOT NULL COMMENT 'Id анонса',
	`is_global`             TINYINT(1)                NOT NULL COMMENT 'Является ли анонс глобальным',
	`status`                INT(11)                   NOT NULL COMMENT 'Статус анонса',
	`company_id`            INT(11)                   NOT NULL COMMENT 'Id компании',
	`expires_at`            INT(11)                   NOT NULL COMMENT 'Время истечения анонса',
	`type`                  INT(11)                   NOT NULL COMMENT 'Тип анонса',
	`priority`              INT(11)                   NOT NULL COMMENT 'Приоритет анонса',
	`created_at`            INT(11)                   NOT NULL COMMENT 'Время создания анонса',
	`updated_at`            INT(11)                   NOT NULL COMMENT 'Время редактирования анонса',
	`resend_repeat_time`    INT(11)                   NOT NULL COMMENT 'Период для повторной отправки анонса',
	`receiver_user_id_list` JSON                      NOT NULL COMMENT 'Пользователи которые получат анонс',
	`excluded_user_id_list` JSON                      NOT NULL COMMENT 'Пользователи которые не должны получить анонс',
	`extra`                 JSON                      NOT NULL COMMENT 'Доп. данные',

	PRIMARY KEY (`announcement_id`),

	INDEX get_by_status (`status`, `is_global`, `company_id`, `priority`) COMMENT 'чтение для пользователей',
	INDEX get_by_type (`status`, `type`, `company_id`),
	INDEX get_expired (`status`, `expires_at`),
	INDEX get_to_resend (`status`, `resend_repeat_time`)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'Таблица для анонсов';