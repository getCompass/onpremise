USE `pivot_sms_service` ;

CREATE TABLE IF NOT EXISTS `pivot_sms_service`.`send_queue` (
	`sms_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'uuid идентификатор отправляемого сообщения',
	`stage` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'этап выполнения задачи: 0 – need_send_sms, 1 – need_check_sms',
	`need_work` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда необходимо выполнить задачу',
	`error_count` TINYINT(4) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'количество ошибок выполнения задачи',
	`created_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка в микросекундах, когда была создана задача',
	`updated_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда задача была обновлена',
	`task_expire_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда задача на отправку становится не актуальной',
	`phone_number` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'номер телефона, куда отправляем смс',
	`text` VARCHAR(1024) NOT NULL DEFAULT '' COMMENT 'отправляемый текст',
	`provider_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор провайдера, который отправляет смс',
	`extra` JSON NOT NULL COMMENT 'экстра информация к задаче, например список провайдеров, которых нужно исключить при выборе провайдера для отправки; идентификатор смс в системе провайдера для отслежки статуса отправки',
	PRIMARY KEY (`sms_id`) COMMENT 'первичный ключ',
	INDEX `cron_sms_dispatcher` (`need_work` ASC, `error_count` ASC) COMMENT 'индекс для крона, который занимается отправкой смс и их дальнейшим отслеживанием отправки')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'задачи на отправку смс и их дальнейшее отслеживание';

CREATE TABLE IF NOT EXISTS `pivot_sms_service`.`send_history` (
	`row_id` BIGINT(20) AUTO_INCREMENT COMMENT 'идентификатор записи',
	`sms_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'uuid идентификатор сообщения',
	`is_success` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 – успешная ли отправка смс',
	`task_created_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка в микросекундах, когда была создана задача',
	`send_to_provider_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка в микросекундах, когда реквест был отправлен провайдеру',
	`sms_sent_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка в микросекундах, когда провайдер отправил смс',
	`created_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`provider_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор провайдера',
	`provider_response_code` INT(11) DEFAULT 0 NOT NULL COMMENT 'http status code полученный при отправке API-запроса к провайдеру',
	`provider_response` JSON NOT NULL COMMENT 'json response полученный при отправке сообщения через шлюз провайдера',
	`extra_alias` JSON NOT NULL COMMENT 'копия экстры информации к задаче на отправку смс',
	PRIMARY KEY (`row_id`) COMMENT 'первичный ключ',
	INDEX `get_all_by_sms_id` (`sms_id` ASC) COMMENT 'индекс для выборки по sms_id')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'история попыток отправки смс сообщений';

CREATE TABLE IF NOT EXISTS `pivot_sms_service`.`provider_list` (
	`provider_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор провайдера',
	`is_available` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 – доступен ли провайдер для отправки новых смс сообщений',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 – удален ли провайдер из системы',
	`created_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL COMMENT 'версионная json-структура, содержащая например релевантную оценку провайдера, которая высчитывается кроном-наблюдателем на основе статистики доставки смс',
	PRIMARY KEY (`provider_id`) COMMENT 'первичный ключ')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'список существующих провайдеров в проекте';

CREATE TABLE IF NOT EXISTS `pivot_sms_service`.`observe_provider_list_task` (
	`provider_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор провайдера',
	`need_work` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда необходимо выполнить задачу',
	`created_at` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`extra` JSON NOT NULL COMMENT 'версионная json-структура, содержащая экстра-информацию о задаче',
	PRIMARY KEY (`provider_id`) COMMENT 'первичный ключ',
	INDEX `cron_sms_provider_observer` (`need_work` ASC) COMMENT 'индекс для крона, который занимается наблюдением за состоянием провайдера')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'задачи для наблюдения за провайдерами';