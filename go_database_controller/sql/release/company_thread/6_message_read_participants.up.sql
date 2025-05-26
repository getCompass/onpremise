use company_thread;

CREATE TABLE IF NOT EXISTS `message_read_participants_1` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_2` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_3` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_4` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_5` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_6` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_7` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_8` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_9` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_10` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_11` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';


CREATE TABLE IF NOT EXISTS `message_read_participants_12` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'мапа чата',
	`thread_message_index` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'индекс сообщения',
	`user_id` BIGINT NOT NULL COMMENT 'id пользователя',
	`read_at` INT NOT NULL DEFAULT 0 COMMENT 'дата просмотра',
	`message_created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'мапа сообщения',
	PRIMARY KEY (`thread_map`, `thread_message_index`, `user_id`),
	INDEX `message_map` (`message_map` ASC) COMMENT 'мапа сообщения',
	INDEX `message_created_at` (`message_created_at` ASC) COMMENT 'дата создания сообщения'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица факта просмотра сообщений от опльзователя';

ALTER TABLE `thread_dynamic` ADD COLUMN `last_read_message` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'информация о последнем просмотренном сообщении в чате' AFTER `updated_at`;