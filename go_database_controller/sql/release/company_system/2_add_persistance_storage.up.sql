use `company_system`;

CREATE TABLE IF NOT EXISTS `async_task` (
	`async_task_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор задачи',
	`is_failed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг проваленности задачи',
	`task_type` INT(11) NOT NULL COMMENT 'тип задачи (10 обычная, 20 постоянная)',
	`need_work_at` INT(11) NOT NULL COMMENT 'Время следующей итерации задачи',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Число ошибок задачи',
	`created_at` INT(11) NOT NULL COMMENT 'Дата создания задачи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Дата последнего изменения задачи',
	`module` VARCHAR(64) NOT NULL COMMENT 'Целевой модуль, куда нужно отправить задачу',
	`group` VARCHAR(64) NOT NULL COMMENT 'Группа, к которой принадлежит задача',
	`name` VARCHAR(128) NOT NULL COMMENT 'читаемое название задачи',
	`unique_key` VARCHAR(256) NOT NULL COMMENT 'Уникальный ключ задачи',
	`data` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'Данные задачи',
	PRIMARY KEY (`async_task_id`),
	UNIQUE INDEX `uniq_protector` (`unique_key` ASC),
	INDEX `get_by_need_work` (`is_failed` ASC, `need_work_at` ASC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COMMENT='таблица для хранения асинхронных задач go event';

CREATE TABLE IF NOT EXISTS `async_task_history` (
	`async_task_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор задачи',
	`is_failed` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг проваленности задачи',
	`task_type` INT(11) NOT NULL COMMENT 'тип задачи (10 обычная, 20 постоянная)',
	`need_work_at` INT(11) NOT NULL COMMENT 'Время следующей итерации задачи',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Число ошибок задачи',
	`created_at` INT(11) NOT NULL COMMENT 'Дата создания задачи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Дата последнего изменения задачи',
	`module` VARCHAR(64) NOT NULL COMMENT 'Целевой модуль, куда нужно отправить задачу',
	`group` VARCHAR(64) NOT NULL COMMENT 'Группа, к которой принадлежит задача',
	`name` VARCHAR(128) NOT NULL COMMENT 'читаемое название задачи',
	`unique_key` VARCHAR(256) NOT NULL COMMENT 'Уникальный ключ задачи',
	`data` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'Данные задачи',
	PRIMARY KEY (`async_task_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COMMENT='таблица для хранения истотрии асинхронных задач go event';