USE `pivot_mail_service`;

CREATE TABLE IF NOT EXISTS `pivot_mail_service`.`send_queue` (
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'uuid идентификатор отправляемого сообщения',
	`stage` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'этап выполнения задачи',
	`need_work` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда необходимо выполнить задачу',
	`error_count` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'количество ошибок выполнения задачи',
	`created_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка в микросекундах, когда была создана задача',
	`updated_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда задача была обновлена',
	`task_expire_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда задача на отправку становится не актуальной',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'почта',
	`title` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT 'отправляемый заголовок',
	`content` MEDIUMTEXT NOT NULL COMMENT 'отправляемое содержимое',
	`extra` JSON NOT NULL COMMENT 'дополнительная информация',
	PRIMARY KEY (`message_id`),
	INDEX `cron_mail_dispatcher` (`need_work` ASC, `error_count` ASC) COMMENT 'индекс для выборки задач на отправку писем')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Таблица с задачами на отправку электронных писем';
