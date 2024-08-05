USE `pivot_auth_2028`;

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_1` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_1` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_1` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_1` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_2` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_2` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_2` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_2` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_3` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_3` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_3` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_3` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_4` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_4` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_4` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_4` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_5` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_5` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_5` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_5` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_6` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_6` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_6` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_6` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_7` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_7` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_7` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_7` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_8` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_8` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_8` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_8` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_9` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_9` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_9` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_9` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_10` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_10` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_10` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_10` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_11` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_11` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_11` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_11` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_list_12` (
	`auth_uniq` VARCHAR(36) NOT NULL COMMENT 'уникальный ключ аутентификации',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли аутентификация',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип (логин/регистрация)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш user-agent',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP',
	PRIMARY KEY (`auth_uniq`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_phone_list_12` (
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
	PRIMARY KEY (`auth_map`),
	INDEX `created_at` (`created_at`) COMMENT 'индекс по времени создания')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных об отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_list_12` (
	`2fa_map` VARCHAR(255) NOT NULL COMMENT 'map подтверждения номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`company_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`is_active` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'активный ли токен',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешно ли выполнено действие',
	`action_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип 2fa действия',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда обновлена запись',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда попытка аутентификации истекает',
	PRIMARY KEY (`2fa_map`),
	INDEX `get_unused` (`expires_at`, `is_success`) COMMENT 'индекс для получения неиспользованных попыток',
	INDEX `user_company` (`user_id`, `company_id`, `action_type`, `created_at`) COMMENT 'индекс для получения записей пользователей в компаниях')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории 2fa действий';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`2fa_phone_list_12` (
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
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_1` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_2` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_3` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_4` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_5` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_6` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_7` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_8` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_9` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_10` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_11` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_mail_list_12` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`is_success` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'успешная ли попытка',
	`has_password` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен пароль в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`has_code` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'введен проверочный код в процессе авторизации/регистрации через почту (служит для определения пройден ли данный этап)',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправленных писем',
	`password_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных паролей',
	`code_error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество неправильно введеных кодов подтверждений',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, после которого можно переотправить код',
	`message_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'идентификатор отправленного сообщения на почту пользователя',
	`code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хеш кода',
	`mail` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'адрес почты',
	PRIMARY KEY (`auth_map`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью почты';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_1` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_2` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_3` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_4` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_5` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_6` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_7` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_8` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_9` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_10` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_11` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2028`.`auth_sso_list_12` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';
