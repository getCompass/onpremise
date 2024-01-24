USE `pivot_rating_10m`;

DROP TABLE IF EXISTS `screen_time_raw_list_1`;
DROP TABLE IF EXISTS `screen_time_raw_list_2`;
DROP TABLE IF EXISTS `screen_time_raw_list_3`;
DROP TABLE IF EXISTS `screen_time_raw_list_4`;
DROP TABLE IF EXISTS `screen_time_raw_list_5`;
DROP TABLE IF EXISTS `screen_time_raw_list_6`;
DROP TABLE IF EXISTS `screen_time_raw_list_7`;
DROP TABLE IF EXISTS `screen_time_raw_list_8`;
DROP TABLE IF EXISTS `screen_time_raw_list_9`;
DROP TABLE IF EXISTS `screen_time_raw_list_10`;

DROP TABLE IF EXISTS `screen_time_user_day_list_1`;
DROP TABLE IF EXISTS `screen_time_user_day_list_2`;
DROP TABLE IF EXISTS `screen_time_user_day_list_3`;
DROP TABLE IF EXISTS `screen_time_user_day_list_4`;
DROP TABLE IF EXISTS `screen_time_user_day_list_5`;
DROP TABLE IF EXISTS `screen_time_user_day_list_6`;
DROP TABLE IF EXISTS `screen_time_user_day_list_7`;
DROP TABLE IF EXISTS `screen_time_user_day_list_8`;
DROP TABLE IF EXISTS `screen_time_user_day_list_9`;
DROP TABLE IF EXISTS `screen_time_user_day_list_10`;

DROP TABLE IF EXISTS `screen_time_space_day_list_1`;
DROP TABLE IF EXISTS `screen_time_space_day_list_2`;
DROP TABLE IF EXISTS `screen_time_space_day_list_3`;
DROP TABLE IF EXISTS `screen_time_space_day_list_4`;
DROP TABLE IF EXISTS `screen_time_space_day_list_5`;
DROP TABLE IF EXISTS `screen_time_space_day_list_6`;
DROP TABLE IF EXISTS `screen_time_space_day_list_7`;
DROP TABLE IF EXISTS `screen_time_space_day_list_8`;
DROP TABLE IF EXISTS `screen_time_space_day_list_9`;
DROP TABLE IF EXISTS `screen_time_space_day_list_10`;

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_1` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_2` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_3` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_4` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_5` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_6` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_7` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_8` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_9` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_10` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `user_local_time` VARCHAR(255) NOT NULL COMMENT 'локальное время начала 15-ти минутки в которую пользователь был онлайн (строковая: 03.07.2023 08:00)',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `user_local_time`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `user_local_time` (`user_local_time` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_1` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_2` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_3` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_4` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_5` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_6` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_7` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_8` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_9` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_10` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователя за текущий день',
    PRIMARY KEY (`user_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_1` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_2` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_3` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_4` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_5` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_6` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_7` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_8` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_9` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_10` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `user_local_date` VARCHAR(255) NOT NULL COMMENT 'строковая дата локального времени пользователя (например: 03.07.2023)',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `screen_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'активность пользователей в пространстве за текущий день',
    PRIMARY KEY (`space_id`, `user_local_date`),
    INDEX `user_local_date` (`user_local_date` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';
