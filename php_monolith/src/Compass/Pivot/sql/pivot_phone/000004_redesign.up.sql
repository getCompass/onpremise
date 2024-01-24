USE `pivot_phone`;

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_change_story` (
	`change_phone_story_id` BIGINT(20) AUTO_INCREMENT NOT NULL COMMENT 'id смены номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус смены номера',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап смены номера',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда смена номера истечет',
	`session_uniq` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'сессия, к которой смена привязана',
	PRIMARY KEY (`change_phone_story_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории смен номеров телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_change_via_sms_story` (
	`change_phone_story_id` BIGINT(20) NOT NULL COMMENT 'id смены номера телефона',
	`phone_number` VARCHAR(45) NOT NULL COMMENT 'номер телефона',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'завершено ли подтверждение номера',
	`stage` INT(11) NOT NULL DEFAULT 0 COMMENT 'этап смены номера',
	`resend_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переотправок смс',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления',
	`next_resend_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, когда можно переотправить',
	`sms_id` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'id смски',
	`sms_code_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'хэш смс кода',
	PRIMARY KEY (`change_phone_story_id`, `phone_number`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории подтверждения номеров для смены номера';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_0` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_1` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_2` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_3` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_4` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_5` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_6` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_7` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_8` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_9` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения хешей телефонов';