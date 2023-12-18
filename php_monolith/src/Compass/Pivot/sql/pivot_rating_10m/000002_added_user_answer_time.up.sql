USE `pivot_rating_10m`;

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_1` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_2` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_3` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_4` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_5` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_6` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_7` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_8` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_9` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_raw_list_10` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `answer_at` INT(11) NOT NULL COMMENT 'время когда пользователь ответил',
    `conversation_key` VARCHAR(255) NOT NULL COMMENT 'диалог в котором ответил пользователь',
    `answer_time` INT(11) NOT NULL DEFAULT 0 COMMENT 'время ответа пользователя на сообщение в секундах',
    `space_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пространства, в котором был ответ',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    PRIMARY KEY (`user_id`, `answer_at`, `conversation_key`),
    INDEX `created_at` (`created_at` ASC),
    INDEX `answer_at` (`answer_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения сырой статистики по времени ответа пользователя на сообщение';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_1` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_2` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_3` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_4` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_5` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_6` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_7` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_8` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_9` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_user_day_list_10` (
    `user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы пользователя за день',
    PRIMARY KEY (`user_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа пользователя на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_1` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_2` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_3` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_4` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_5` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_6` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_7` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_8` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_9` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';

CREATE TABLE IF NOT EXISTS `message_answer_time_space_day_list_10` (
    `space_id` BIGINT(20) NOT NULL COMMENT 'id пространства',
    `day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня',
    `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
    `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
    `answer_time_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'ответы в пространстве за день',
    PRIMARY KEY (`space_id`, `day_start_at`),
    INDEX `day_start_at` (`day_start_at` ASC)
) ENGINE = InnoDB CHARACTER SET = utf8 COMMENT 'таблица для хранения статистики по времени ответа в пространстве на сообщение по дням';
