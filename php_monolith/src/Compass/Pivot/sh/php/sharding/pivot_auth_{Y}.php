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
-- region pivot_auth_{Y}
-- -----------------------------------------------------\n\n";

for ($i = 0; $i < $limit_months; $i++) {
	$tt    = monthStart($start_time + DAY1 * 31 * $i);
	$year  = date("Y", $tt);
	$month = date("n", $tt);

	// генерация sql кода
	$output .= "CREATE SCHEMA IF NOT EXISTS `pivot_auth_$year` DEFAULT CHARACTER SET utf8; USE `pivot_auth_$year`;\n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_auth_$year`.`auth_list_$month` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_auth_$year`.`auth_phone_list_$month` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'удачное ли подтверждение номера',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных смс',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда можно переотправить смс',
	`sms_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор смс-сообщения, которым был отправлен проверочный код',
	`sms_code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш кода смс',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_auth_$year`.`2fa_list_$month` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий'; \n \n";

	$output .= "CREATE TABLE IF NOT EXISTS `pivot_auth_$year`.`2fa_phone_list_$month` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'удачное ли подтверждение номера',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных смс',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда можно переотправить смс',
	`sms_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор смс-сообщения, которым был отправлен проверочный код',
	`sms_code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш кода смс',
	`phone_number` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'номер телефона',
	PRIMARY KEY (`2fa_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон'; \n \n";
}

$output .= "-- -----------------------------------------------------
-- endregion pivot_auth_{Y}
-- ----------------------------------------------------- \n \n";

file_put_contents($file_path, $output, FILE_APPEND);