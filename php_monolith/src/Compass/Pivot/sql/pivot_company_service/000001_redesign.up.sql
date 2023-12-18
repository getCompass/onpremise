USE `pivot_company_service`;

CREATE TABLE IF NOT EXISTS `company_service_task` (
	`task_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'id задачи',
        `is_failed` TINYINT(1) NOT NULL COMMENT 'флаг неуспешного завершения выполнения задачи',
	`need_work` INT(11) NOT NULL COMMENT 'когда нужно выполнить задачу',
	`type` INT(11) NOT NULL COMMENT 'тип задачи',
        `started_at` INT(11) NOT NULL COMMENT 'время начала задачи',
        `finished_at` INT(11) NOT NULL COMMENT 'время окончания задачи',
	`created_at` INT(11) NOT NULL COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL COMMENT 'время изменения записи',
        `company_id` BIGINT(20) NOT NULL COMMENT 'id компании',
        `logs` MEDIUMTEXT NOT NULL COMMENT 'логи выполнения задачи',
        `data` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`task_id`),
	INDEX `need_work` (`is_failed`, `need_work` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'очередь сервисных задач для компаний';

CREATE TABLE IF NOT EXISTS `company_service_task_history` (
	`task_id` BIGINT(20) NOT NULL COMMENT 'id задачи',
	`is_failed` TINYINT(1) NOT NULL COMMENT 'флаг неуспешного завершения выполнения задачи',
	`need_work` INT(11) NOT NULL COMMENT 'когда нужно выполнить задачу',
	`type` INT(11) NOT NULL COMMENT 'тип задачи',
	`started_at` INT(11) NOT NULL COMMENT 'время начала задачи',
	`finished_at` INT(11) NOT NULL COMMENT 'время окончания задачи',
	`created_at` INT(11) NOT NULL COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL COMMENT 'время изменения записи',
	`company_id` BIGINT(20) NOT NULL COMMENT 'id компании',
	`logs` MEDIUMTEXT NOT NULL COMMENT 'логи выполнения задачи',
	`data` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`task_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'история сервисных задач для компаний';


CREATE TABLE IF NOT EXISTS `domino_registry` (
	`domino_id` VARCHAR(64) NOT NULL COMMENT 'id доминошки',
        `code_host` VARCHAR(255) NOT NULL COMMENT 'хост по которому находится кодовая часть домино',
        `database_host` VARCHAR(255) NOT NULL COMMENT 'хост по которому находится mysql часть домино',
	`is_company_creating_allowed` INT(11) NOT NULL DEFAULT 0 COMMENT 'Разрешено ли создавать пустые компании на этой домино',
	`common_port_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Число обычных портов',
	`service_port_count` INT(11) NOT NULL COMMENT 'Число сервисных портов',
	`reserved_port_count` INT(11) NOT NULL COMMENT 'Число резервных портов',
	`common_active_port_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Число обычных активных портов на домино на текущий момент',
	`reserve_active_port_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Число резеврных активных портов на домино на текущий момент',
	`service_active_port_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Число сервсиных активных портов на домино на текущий момент',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`domino_id`),
	UNIQUE KEY code_host_UNIQUE (code_host),
	UNIQUE KEY database_host_UNIQUE (database_host))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица реестр доминошек';

CREATE TABLE IF NOT EXISTS `company_init_registry` (
	`company_id` BIGINT(20) NOT NULL COMMENT 'id компании',
	`is_vacant` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'Является ли компания свободной',
	`is_deleted` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'Является ли компания удаленной (больше не обслуживает запросы)',
	`is_purged` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'Удалены ли физические файлы компании с сервера',
	`creating_started_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда начался процесс создания компании',
	`creating_finished_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда завершился процесс создания компании',
	`became_vacant_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда компания стала свободной',
	`occupation_started_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда начался процесс занятия компании пользователем',
	`occupation_finished_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда закончился процесс занятия компании пользователем',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда компания была удалена (перестала обслуживать запросы)',
	`purged_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда файлы компании были полностью удалены из системы',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Дата обновления записи',
	`occupant_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'Пользователь, занявший компанию',
	`deleter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'Пользователь, удаливший компанию',
	`logs` MEDIUMTEXT NOT NULL DEFAULT ('') COMMENT 'Логи жизненного цикла компании',
	`extra` JSON NOT NULL DEFAULT (JSON_OBJECT()) COMMENT 'дополнительные данные',
	PRIMARY KEY (`company_id`),
	INDEX `get_vacant` (`is_vacant`),
	INDEX `get_for_purge` (`is_purged`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'реестр жизненного цикла компании';

