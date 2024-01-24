USE `pivot_phone`;

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_00` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_00` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_01` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_01` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_02` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_02` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_03` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_03` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_04` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_04` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_05` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_05` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_06` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_06` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_07` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_07` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_08` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_08` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_09` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_09` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_0a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_0a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_0b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_0b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_0c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_0c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_0d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_0d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_0e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_0e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_0f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_0f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_10` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_10` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_11` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_11` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_12` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_12` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_13` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_13` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_14` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_14` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_15` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_15` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_16` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_16` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_17` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_17` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_18` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_18` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_19` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_19` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_1a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_1a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_1b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_1b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_1c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_1c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_1d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_1d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_1e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_1e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_1f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_1f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_20` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_20` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_21` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_21` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_22` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_22` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_23` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_23` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_24` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_24` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_25` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_25` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_26` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_26` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_27` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_27` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_28` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_28` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_29` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_29` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_2a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_2a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_2b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_2b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_2c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_2c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_2d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_2d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_2e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_2e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_2f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_2f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_30` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_30` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_31` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_31` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_32` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_32` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_33` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_33` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_34` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_34` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_35` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_35` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_36` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_36` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_37` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_37` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_38` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_38` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_39` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_39` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_3a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_3a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_3b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_3b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_3c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_3c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_3d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_3d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_3e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_3e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_3f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_3f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_40` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_40` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_41` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_41` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_42` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_42` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_43` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_43` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_44` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_44` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_45` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_45` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_46` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_46` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_47` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_47` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_48` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_48` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_49` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_49` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_4a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_4a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_4b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_4b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_4c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_4c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_4d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_4d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_4e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_4e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_4f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_4f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_50` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_50` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_51` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_51` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_52` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_52` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_53` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_53` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_54` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_54` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_55` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_55` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_56` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_56` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_57` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_57` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_58` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_58` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_59` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_59` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_5a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_5a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_5b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_5b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_5c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_5c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_5d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_5d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_5e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_5e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_5f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_5f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_60` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_60` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_61` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_61` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_62` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_62` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_63` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_63` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_64` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_64` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_65` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_65` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_66` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_66` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_67` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_67` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_68` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_68` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_69` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_69` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_6a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_6a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_6b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_6b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_6c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_6c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_6d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_6d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_6e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_6e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_6f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_6f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_70` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_70` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_71` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_71` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_72` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_72` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_73` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_73` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_74` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_74` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_75` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_75` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_76` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_76` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_77` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_77` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_78` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_78` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_79` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_79` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_7a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_7a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_7b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_7b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_7c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_7c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_7d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_7d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_7e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_7e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_7f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_7f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_80` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_80` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_81` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_81` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_82` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_82` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_83` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_83` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_84` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_84` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_85` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_85` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_86` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_86` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_87` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_87` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_88` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_88` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_89` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_89` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_8a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_8a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_8b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_8b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_8c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_8c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_8d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_8d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_8e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_8e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_8f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_8f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_90` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_90` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_91` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_91` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_92` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_92` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_93` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_93` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_94` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_94` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_95` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_95` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_96` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_96` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_97` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_97` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_98` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_98` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_99` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_99` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_9a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_9a` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_9b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_9b` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_9c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_9c` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_9d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_9d` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_9e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_9e` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_9f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_9f` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a0` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a1` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a2` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a3` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a4` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a5` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a6` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a7` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a8` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_a9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_a9` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_aa` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_aa` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ab` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ab` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ac` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ac` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ad` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ad` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ae` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ae` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_af` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_af` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b0` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b1` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b2` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b3` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b4` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b5` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b6` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b7` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b8` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_b9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_b9` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ba` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ba` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_bb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_bb` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_bc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_bc` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_bd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_bd` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_be` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_be` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_bf` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_bf` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c0` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c1` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c2` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c3` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c4` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c5` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c6` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c7` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c8` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_c9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_c9` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ca` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ca` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_cb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_cb` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_cc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_cc` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_cd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_cd` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ce` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ce` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_cf` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_cf` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d0` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d1` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d2` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d3` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d4` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d5` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d6` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d7` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d8` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_d9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_d9` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_da` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_da` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_db` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_db` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_dc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_dc` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_dd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_dd` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_de` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_de` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_df` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_df` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e0` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e1` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e2` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e3` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e4` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e5` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e6` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e7` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e8` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_e9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_e9` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ea` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ea` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_eb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_eb` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ec` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ec` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ed` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ed` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ee` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ee` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ef` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ef` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f0` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f1` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f2` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f3` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f4` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f5` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f6` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f7` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f8` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_f9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_f9` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_fa` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_fa` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_fb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_fb` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_fc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_fc` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_fd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_fd` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_fe` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_fe` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';



CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_uniq_list_ff` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш номера телефона',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи истории аутентификаций/регистраций';

CREATE TABLE IF NOT EXISTS `pivot_phone`.`invite_list_ff` (
	`invite_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id приглашения',
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'хэш номера телефона',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id компании',
	`inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пригласил',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который получил приглашение',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'статус приглашения',
	`extra` JSON NOT NULL COMMENT 'доп данные',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	PRIMARY KEY (`invite_id`),
	INDEX `phone_number_hash` (`phone_number_hash` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для записи данных о отправленных смс на телефон';