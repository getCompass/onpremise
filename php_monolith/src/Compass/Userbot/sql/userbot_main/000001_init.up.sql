USE `userbot_main`;

CREATE TABLE IF NOT EXISTS `request_list` (
    `request_id`    VARCHAR(36)  NOT NULL DEFAULT '' COMMENT 'идентификатор запроса',
    `token`         VARCHAR(23)  NOT NULL DEFAULT '' COMMENT 'токен для запроса',
    `status`        TINYINT(4)   NOT NULL DEFAULT 0 COMMENT 'статус запроса',
    `error_count`   INT(11)      NOT NULL DEFAULT 0 COMMENT 'количество ошибок при выполнении запроса',
    `need_work`     INT(11)      NOT NULL DEFAULT 0 COMMENT 'время, когда запрос будет взят в работу',
    `created_at`    INT(11)      NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
    `updated_at`    INT(11)      NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
    `request_data`  JSON         NOT NULL COMMENT 'произвольные данные для запроса',
    `result_data`   JSON         NOT NULL COMMENT 'произвольные данные результата выполнения запроса',
    PRIMARY KEY (`request_id`, `token`),
    INDEX `get_by_need_work` (`need_work` ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для запросов внешнего сервиса';

CREATE TABLE IF NOT EXISTS `command_queue` (
    `task_id`       BIGINT(20)  NOT NULL AUTO_INCREMENT COMMENT 'идентификатор задачи',
    `error_count`   INT(11)     NOT NULL DEFAULT 0 COMMENT 'количество ошибок при выполнении задачи',
    `need_work`     INT(11)     NOT NULL DEFAULT 0 COMMENT 'время, когда задача будет взят в работу',
    `created_at`    INT(11)     NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
    `params`        JSON        NOT NULL COMMENT 'произвольные данные для задачи',
    PRIMARY KEY (`task_id`),
    INDEX `get_by_need_work` (`need_work` ASC)
)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT = 'таблица для очереди команд для внешнего сервиса';