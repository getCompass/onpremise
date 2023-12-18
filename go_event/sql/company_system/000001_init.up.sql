use `company_system`;

CREATE TABLE IF NOT EXISTS `go_event_subscriber_list` (
	`subscriber` varchar(255) NOT NULL COMMENT 'идентификатор подписчика',
	`created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата изменения записи',
	`subscription_list` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список подписок',
	PRIMARY KEY (`subscriber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='таблица для хранения списка подписок для go_event';
