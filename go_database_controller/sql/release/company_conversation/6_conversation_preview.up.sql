use company_conversation;

CREATE TABLE IF NOT EXISTS `conversation_preview` (

	`parent_type` TINYINT NOT NULL COMMENT 'тип родителя сообщения',
	`parent_message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения-родителя',
	`is_deleted` TINYINT NOT NULL DEFAULT 0 COMMENT 'удалено ли превью',
	`conversation_message_created_at` INT NOT NULL COMMENT 'время создания сообщения в чате',
	`parent_message_created_at` INT NOT NULL COMMENT 'время создания сообщения-родителя',
	`created_at` INT NOT NULL COMMENT 'время создания записи',
	`updated_at` INT NOT NULL COMMENT 'время обновления записи',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя, добавившего превью',
	`preview_map` VARCHAR(255) NOT NULL COMMENT 'мапа превью',
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор диалога',
        `conversation_message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения-родителя чата',
	`hidden_by_user_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'id пользователей, скрывших превью',
	PRIMARY KEY (`parent_type`, `parent_message_map`),
	INDEX `get_previews` (`conversation_map` ASC,`is_deleted` ASC, `parent_message_created_at` ASC, `parent_type` ASC, `conversation_message_created_at` ASC, `user_id` ASC),
        INDEX `conversation_message_map` (`conversation_message_map` ASC)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='связующая таблица между превью и чатом';

DROP INDEX `get_files` ON `conversation_file`;
CREATE INDEX `get_files` ON `conversation_file` (`conversation_map` ASC, `is_deleted` ASC, `row_id` ASC, `user_id` ASC, `file_type` ASC, `parent_type` ASC, `conversation_message_created_at` ASC);