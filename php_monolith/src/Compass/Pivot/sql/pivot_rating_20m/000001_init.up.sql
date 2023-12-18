USE `pivot_rating_20m`;

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_11` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_12` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_13` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_14` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_15` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_16` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_17` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_18` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_19` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_raw_list_20` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором был онлайн',
    `online_at` INT(11) NOT NULL COMMENT 'время в которое был онлайн',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время, сколько добавляем пользователю',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `space_id`, `online_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `online_at` (`online_at` ASC),
    INDEX `space_id.online_at` (`space_id` ASC, `online_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по экранному времени';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_11` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_12` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_13` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_14` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_15` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_16` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_17` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_18` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_19` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_user_day_list_20` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время пользователя за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_11` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_12` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_13` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_14` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_15` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_16` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_17` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_18` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_19` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `screen_time_space_day_list_20` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `screen_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'экранное время в пространстве за день',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по экранному времени по дням в пространствах';

CREATE TABLE IF NOT EXISTS `action_raw_list_11` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_12` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_13` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_14` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_15` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_16` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_17` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_18` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_19` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_raw_list_20` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства, в котором были действия',
    `action_at` INT(11) NOT NULL COMMENT 'начало 15-ти минутки в которую совершал действия',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `space_id`, `action_at`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `action_at` (`action_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по действиям';

CREATE TABLE IF NOT EXISTS `action_user_day_list_11` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_12` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_13` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_14` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_15` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_16` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_17` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_18` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_19` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_user_day_list_20` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий пользователя',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям пользователя по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_11` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_12` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_13` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_14` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_15` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_16` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_17` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_18` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_19` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';

CREATE TABLE IF NOT EXISTS `action_space_day_list_20` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `action_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество действий в пространстве',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по действиям в пространстве по дням';
