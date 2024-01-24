use company_thread;

CREATE TABLE IF NOT EXISTS `message_block_remind_list` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор треда',
	`block_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор блока',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	`remind_data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле содержит JSON структуру с Напоминаниями на сообщения блока',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Напоминания для блока сообщений';