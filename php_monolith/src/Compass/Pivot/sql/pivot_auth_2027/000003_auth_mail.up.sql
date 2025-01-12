USE `pivot_auth_2027`;

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_1` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_2` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_3` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_4` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_5` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_6` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_7` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_8` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_9` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_10` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_11` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_mail_list_12` (
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
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_1` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_2` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_3` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_4` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_5` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_6` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_7` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_8` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_9` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_10` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_11` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';

CREATE TABLE IF NOT EXISTS `pivot_auth_2027`.`auth_sso_list_12` (
	`auth_map` VARCHAR(255) NOT NULL COMMENT 'map аутентификации',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Токен попытки аутентификации через SSO',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	PRIMARY KEY (`auth_map`),
	UNIQUE KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'Таблица с попытками аутентификации с помощью SSO';