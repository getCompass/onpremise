/* @formatter:off */
/* таблица очереди задач подготовки сущностей к индексации */
CREATE TABLE IF NOT EXISTS `entity_preparation_task_queue` (
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
COMMENT='таблица очереди задач подготовки сущностей к индексации';

/* удаляем старую таблицу (к сожалению уже релизнули на стейдж, так что только так */
DROP TABLE IF EXISTS `entity_search_id_rel`;

/* таблица связей ключей сущностей приложения поиска */
CREATE TABLE IF NOT EXISTS `entity_search_id_rel_0` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_0` AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_1` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_1` AUTO_INCREMENT=1000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_2` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_2` AUTO_INCREMENT=2000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_3` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_3` AUTO_INCREMENT=3000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_4` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_4` AUTO_INCREMENT=4000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_5` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_5` AUTO_INCREMENT=5000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_6` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_6` AUTO_INCREMENT=6000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_7` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_7` AUTO_INCREMENT=7000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_8` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_8` AUTO_INCREMENT=8000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_9` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_9` AUTO_INCREMENT=9000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_a` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_a` AUTO_INCREMENT=10000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_b` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_b` AUTO_INCREMENT=11000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_c` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_c` AUTO_INCREMENT=12000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_d` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_d` AUTO_INCREMENT=13000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_e` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_e` AUTO_INCREMENT=14000000000;

CREATE TABLE IF NOT EXISTS `entity_search_id_rel_f` (
	`entity_id`   VARCHAR(40)  NOT NULL                COMMENT 'hash-сумма от entity_map',
	`search_id`   BIGINT(20)   NOT NULL AUTO_INCREMENT COMMENT 'числовой идентификатор для поиска',
	`entity_type` INT(11)      NOT NULL                COMMENT 'тип сущности приложения',
	`entity_map`  VARCHAR(255) NOT NULL                COMMENT 'map идентификатор сущности приложения',
	PRIMARY KEY (`entity_id`),
	UNIQUE KEY `search_id` (`search_id` ASC)
	)
	ENGINE=InnoDB
	DEFAULT CHARSET=utf8
	COMMENT='таблица связей идентификаторов сущностей приложения и числовых поисковых идентификаторов';
ALTER TABLE `entity_search_id_rel_f` AUTO_INCREMENT=15000000000;
