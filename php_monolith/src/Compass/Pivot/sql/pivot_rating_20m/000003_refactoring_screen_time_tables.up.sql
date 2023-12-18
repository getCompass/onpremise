USE `pivot_rating_20m`;

DROP TABLE IF EXISTS `screen_time_raw_list_11`;
DROP TABLE IF EXISTS `screen_time_raw_list_12`;
DROP TABLE IF EXISTS `screen_time_raw_list_13`;
DROP TABLE IF EXISTS `screen_time_raw_list_14`;
DROP TABLE IF EXISTS `screen_time_raw_list_15`;
DROP TABLE IF EXISTS `screen_time_raw_list_16`;
DROP TABLE IF EXISTS `screen_time_raw_list_17`;
DROP TABLE IF EXISTS `screen_time_raw_list_18`;
DROP TABLE IF EXISTS `screen_time_raw_list_19`;
DROP TABLE IF EXISTS `screen_time_raw_list_20`;

DROP TABLE IF EXISTS `screen_time_user_day_list_11`;
DROP TABLE IF EXISTS `screen_time_user_day_list_12`;
DROP TABLE IF EXISTS `screen_time_user_day_list_13`;
DROP TABLE IF EXISTS `screen_time_user_day_list_14`;
DROP TABLE IF EXISTS `screen_time_user_day_list_15`;
DROP TABLE IF EXISTS `screen_time_user_day_list_16`;
DROP TABLE IF EXISTS `screen_time_user_day_list_17`;
DROP TABLE IF EXISTS `screen_time_user_day_list_18`;
DROP TABLE IF EXISTS `screen_time_user_day_list_19`;
DROP TABLE IF EXISTS `screen_time_user_day_list_20`;

DROP TABLE IF EXISTS `screen_time_space_day_list_11`;
DROP TABLE IF EXISTS `screen_time_space_day_list_12`;
DROP TABLE IF EXISTS `screen_time_space_day_list_13`;
DROP TABLE IF EXISTS `screen_time_space_day_list_14`;
DROP TABLE IF EXISTS `screen_time_space_day_list_15`;
DROP TABLE IF EXISTS `screen_time_space_day_list_16`;
DROP TABLE IF EXISTS `screen_time_space_day_list_17`;
DROP TABLE IF EXISTS `screen_time_space_day_list_18`;
DROP TABLE IF EXISTS `screen_time_space_day_list_19`;
DROP TABLE IF EXISTS `screen_time_space_day_list_20`;

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_11` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_12` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_13` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_14` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_15` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_16` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_17` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_18` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_19` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_20` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_11` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_12` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_13` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_14` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_15` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_16` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_17` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_18` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_19` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_20` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_11` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_12` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_13` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_14` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_15` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_16` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_17` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_18` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_19` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_20` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';
