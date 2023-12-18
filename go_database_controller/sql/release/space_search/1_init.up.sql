/* @formatter:off */
/* таблица связей ключей сущностей приложения поиска */
CREATE TABLE IF NOT EXISTS `entity_search_id_rel` (
    `search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
    `entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
    `entity_id`   VARCHAR(255) NOT NULL                COMMENT 'идентификатор сущности приложения',
    PRIMARY KEY (`search_id`),
    UNIQUE KEY `entity_type.entity_id` (`entity_type` ASC, `entity_id` ASC)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';

/* таблица очереди задач индексации */
CREATE TABLE IF NOT EXISTS `index_task_queue` (
    `task_id`     BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор задачи',
    `type`        INT(11)    NOT NULL COMMENT 'тип задачи',
    `error_count` INT(11)    NOT NULL DEFAULT 0 COMMENT 'количество ошибок исполнения задачи',
    `created_at`  INT(11)    NOT NULL COMMENT 'дата создания задачи',
    `updated_at`  INT(11)    NOT NULL DEFAULT 0 COMMENT 'дата обновления записи задачи',
    `data`        JSON       NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'данные задачи',
    PRIMARY KEY (`task_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8
COMMENT='таблица очереди задач индексации';
