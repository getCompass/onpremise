-- -------------------------------------------------------

CREATE SCHEMA IF NOT EXISTS `pivot_phone` DEFAULT CHARACTER SET utf8;
USE `pivot_phone`;

CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_00` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_01` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_02` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_03` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_04` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_05` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_06` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_07` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_08` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_09` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_0a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_0b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_0c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_0d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_0e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_0f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_10` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_11` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_12` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_13` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_14` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_15` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_16` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_17` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_18` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_19` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_1a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_1b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_1c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_1d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_1e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_1f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_20` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_21` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_22` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_23` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_24` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_25` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_26` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_27` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_28` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_29` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_2a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_2b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_2c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_2d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_2e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_2f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_30` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_31` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_32` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_33` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_34` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_35` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_36` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_37` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_38` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_39` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_3a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_3b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_3c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_3d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_3e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_3f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_40` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_41` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_42` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_43` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_44` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_45` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_46` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_47` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_48` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_49` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_4a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_4b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_4c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_4d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_4e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_4f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_50` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_51` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_52` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_53` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_54` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_55` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_56` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_57` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_58` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_59` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_5a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_5b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_5c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_5d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_5e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_5f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_60` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_61` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_62` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_63` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_64` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_65` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_66` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_67` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_68` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_69` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_6a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_6b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_6c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_6d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_6e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_6f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_70` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_71` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_72` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_73` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_74` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_75` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_76` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_77` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_78` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_79` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_7a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_7b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_7c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_7d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_7e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_7f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_80` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_81` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_82` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_83` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_84` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_85` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_86` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_87` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_88` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_89` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_8a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_8b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_8c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_8d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_8e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_8f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_90` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_91` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_92` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_93` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_94` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_95` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_96` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_97` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_98` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_99` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_9a` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_9b` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_9c` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_9d` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_9e` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_9f` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_a9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_aa` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ab` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ac` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ad` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ae` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_af` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_b9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ba` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_bb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_bc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_bd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_be` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_bf` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_c9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ca` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_cb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_cc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_cd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ce` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_cf` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_d9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_da` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_db` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_dc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_dd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_de` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_df` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_e9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ea` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_eb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ec` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ed` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ee` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ef` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f0` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f1` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f2` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f3` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f4` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f5` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f6` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f7` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f8` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_f9` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_fa` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_fb` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_fc` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_fd` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_fe` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


CREATE TABLE IF NOT EXISTS `pivot_phone`.`partner_invite_rel_ff` (
	`phone_number_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'sha1 хэш-сумма номера телефона приглашенного',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - удалена ли запись',
	`is_accepted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - принят ли инвайт',
	`partner_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'идентификатор партнера – того кто пригласил',
	`partner_fee_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент партнерского вознаграждения',
	`discount_percent` INT(11) NOT NULL DEFAULT '0' COMMENT 'процент предоставленной скидки',
	`expire_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда приглашение считается просроченным',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'временная метка, когда запись была обновлена',
	`extra` JSON NOT NULL,
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения отправленных партнером приглашений собственникам на присоединение в Compass';


-- -------------------------------------------------------
-- endregion
-- -------------------------------------------------------