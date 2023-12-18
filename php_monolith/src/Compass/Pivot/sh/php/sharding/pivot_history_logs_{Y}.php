<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

$file_path = PATH_LOGS . "info/" . basename(__FILE__) . ".sql";

if (file_exists($file_path)) {
	file_put_contents($file_path, "");
}

$start_year   = intval(readline(sprintf("Введите год (по умолчанию: %d): ", date("Y", time()))));
$start_month  = intval(readline("Введите номер стартового месяца: "));
$limit_months = intval(readline("Введите кол-во месяцев, для которых необходимо сгенерировать SQL код: "));

if ($start_year < 1) {
	$start_year = date("Y", time());
}

$start_time = mktime(0, 0, 0, $start_month, 1, $start_year);

$output = "-- -----------------------------------------------------
-- region pivot_history_logs_{Y}
-- -----------------------------------------------------\n\n";

for ($i = 0; $i < $limit_months; $i++) {
	$tt    = monthStart($start_time + DAY1 * 31 * $i);
	$year  = date("Y", $tt);
	$month = date("n", $tt);

	// генерация sql кода
	$output .= "CREATE SCHEMA IF NOT EXISTS `pivot_history_logs_$year` DEFAULT CHARACTER SET utf8; USE `pivot_history_logs_$year`;\n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`user_auth_history` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'auth_map ключ',
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус аутентификации',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
	PRIMARY KEY (`auth_map`, `user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для истории совершенных аутентификаций'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`user_change_phone_history` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время редактирования записи',
	`previous_phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'прошлый номер телефона',
	`new_phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'новый номер телефона',
	`change_phone_story_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ смены номера телефона',
	PRIMARY KEY (`user_id`, `created_at`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для истории изменений номера телефона'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`user_action_history` (
	`row_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'уникальный идентификатор записи',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип действия над профилем пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`row_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения истории совершаемых значимых действий над профилем пользователя'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`session_history` (
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ сессии',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус сессии',
	`login_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь залогинился',
	`logout_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда пользователь разлогинился',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	PRIMARY KEY (`session_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с историей сессий'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`invite_link_validate_history` (
	`history_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор шага истории',
	`invite_link_uniq` VARCHAR(12) NOT NULL DEFAULT '' COMMENT 'идентификатор ссылки-инвайта, который провалидировали, пустой если не смогли провалидировать',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который перешел по ссылке',
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор сессии, с которой был зарегистрирован переход',
	`input_link` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'вся ссылка целиком',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время перехода по ссылке',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`history_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с историей по каждому запросу информации о ссылке'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`invite_link_accepted_history` (
	`history_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор шага истории',
	`invite_link_uniq` VARCHAR(12) NOT NULL DEFAULT '' COMMENT 'идентификатор ссылки-инвайта',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который принял приглашение',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`entry_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор сессии, с которой было принято приглашение',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда было принято приглашение',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`history_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с историей по каждому принятому инвайту-ссылке'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`company_history` (
	`log_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'id лога',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании к которой принадлежит запись лога',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип лога',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'тело лога',
	PRIMARY KEY (`log_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица с историей действий совершаемых с компаниями'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_history_logs_$year`.`send_history` (
	`row_id` BIGINT(20) AUTO_INCREMENT COMMENT 'идентификатор записи',
	`sms_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'uuid идентификатор сообщения',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0/1 – успешная ли отправка смс',
	`task_created_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'временная метка в микросекундах, когда была создана задача',
	`send_to_provider_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'временная метка в микросекундах, когда реквест был отправлен провайдеру',
	`sms_sent_at_ms` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'временная метка в микросекундах, когда провайдер отправил смс',
	`created_at` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана запись',
	`provider_id` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор провайдера',
	`provider_response_code` INT(11) DEFAULT 0 NOT NULL COMMENT 'http status code полученный при отправке API-запроса к провайдеру',
	`provider_response` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json response полученный при отправке сообщения через шлюз провайдера',
	`extra_alias` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'копия экстры информации к задаче на отправку смс',
	PRIMARY KEY (`row_id`) COMMENT 'первичный ключ',
	INDEX `sms_id` (`sms_id` ASC) COMMENT 'индекс для выборки по sms_id')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'история попыток отправки смс сообщений'; \n \n";
}

$output .= "-- -----------------------------------------------------
-- endregion pivot_history_logs_{Y}
-- ----------------------------------------------------- \n \n";

file_put_contents($file_path, $output, FILE_APPEND);