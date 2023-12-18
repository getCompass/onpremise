USE `pivot_business`;

CREATE TABLE IF NOT EXISTS `pivot_business`.`bitrix_user_entity_rel` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'ID пользователя',
	`created_at` INT(11) NOT NULL COMMENT 'когда создана запись',
	`updated_at` INT(11) NOT NULL COMMENT 'когда обновлена запись',
	`bitrix_entity_list` JSON NOT NULL COMMENT 'JSON массив с информацией о каждой созданной сущности',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения информации о созданных сущностях Bitrix для пользователя';

CREATE TABLE IF NOT EXISTS `pivot_business`.`bitrix_user_info_failed_task_list` (
	`task_id` BIGINT(20) NOT NULL COMMENT 'ID задачи в phphooker для добавление/актуализации информации о пользователе в Bitrix',
	`user_id` BIGINT(20) NOT NULL COMMENT 'ID пользователя, информацию которого добавляем/акутализируем',
	`failed_at` INT(11) NOT NULL COMMENT 'Временная метка, когда случилась проблема',
	PRIMARY KEY (`task_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'таблица для хранения проваленных задач на обновление инфомрации о пользователе в сущностях Bitrix';
