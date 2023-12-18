/* делаем это именно в go_event, потому что микросервис кинет ошибку при запуске если таблицы не окажется */
/* php_pivot при этом стартует после go_event, поэтому там накатывать эту миграцию не стоит */

CREATE SCHEMA IF NOT EXISTS `pivot_system` DEFAULT CHARACTER SET utf8;

CREATE TABLE IF NOT EXISTS `pivot_system`.`go_event_subscriber_list` (
	`subscriber` varchar(255) NOT NULL COMMENT 'идентификатор подписчика',
	`created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата изменения записи',
	`subscription_list` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список подписок',
	PRIMARY KEY (`subscriber`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='таблица для хранения списка подписок для go_event';