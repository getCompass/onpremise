-- -----------------------------------------------------
-- region file_node
-- -----------------------------------------------------

USE `file_node` ;

CREATE TABLE IF NOT EXISTS `file_node`.`datastore` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ',
	`extra` JSON NOT NULL COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица с системными значениями для фреймворка, нужна для работы крона general.php, а также по необходимости в нее могут писать другие модули';

CREATE TABLE IF NOT EXISTS `file_node`.`file` (
	`file_key` VARCHAR(255) NOT NULL COMMENT 'map файла',
	`file_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип файла',
	`file_source` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'удален ли файл (bool)',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата загрузки файла',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`size_kb` INT(11) NOT NULL DEFAULT '0' COMMENT 'размер файла в килобайтах',
	`access_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'количество обращений к файлу',
	`last_access_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата последнего обращения к файлу',
	`user_id` BIGINT(20) NOT NULL DEFAULT '0' COMMENT 'id пользователя загрузившего файл',
	`file_hash` varchar(40) NOT NULL DEFAULT '' COMMENT 'Хэш сумма файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`part_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Расположение файла на файловой ноде',
	`extra` JSON NOT NULL COMMENT 'объект с доп информацией в зависимости от типа файла',
	PRIMARY KEY (`file_key`),
	UNIQUE KEY `part_path` (`part_path` ASC) COMMENT 'Уникальный индекс относительного пути файла',
	INDEX `is_deleted, last_access_at` (`is_deleted` ASC, `last_access_at` ASC),
	INDEX `file_hash` (`file_hash` ASC)  COMMENT 'Уникальный индекс хэша файла'
	)
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся все файлы ноды';

CREATE TABLE IF NOT EXISTS `file_node`.`post_upload_queue` (
	`queue_id` INT(11) NOT NULL AUTO_INCREMENT,
	`file_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип файла',
	`error_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'количество обработок записи кроном',
	`need_work` INT(11) NOT NULL DEFAULT '0' COMMENT 'время работы для крона',
	`file_key` VARCHAR(255) NOT NULL COMMENT 'map файла',
	`part_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'часть пути до файла',
	`extra` JSON NOT NULL COMMENT 'дополнительные данные для постобработки',
	PRIMARY KEY (`queue_id`),
	INDEX `need_work` (`need_work` ASC),
	INDEX `file_key` (`file_key` ASC),
	INDEX `cron_postupload` (`need_work` ASC, `file_type` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'очередь на вторичную обработку файлов определенного типа';

CREATE TABLE IF NOT EXISTS `file_node`.`relocate_queue` (
	`file_key` VARCHAR(255) NOT NULL COMMENT 'map файла',
	`error_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'количество обработок записи кроном',
	`need_work` INT(11) NOT NULL DEFAULT '0' COMMENT 'время работы для крона',
	PRIMARY KEY (`file_key`),
	INDEX `need_work, error_count` (`need_work` ASC, `error_count` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'очередь для переноса файлов на текущую ноду';

CREATE TABLE IF NOT EXISTS `file_node`.`unit_test` (
	`key` VARCHAR(32) NOT NULL COMMENT 'Идентификатор',
	`int_row` INT(11) NOT NULL DEFAULT '0' COMMENT 'поле для тестирования значений типа int',
	`extra` JSON NOT NULL COMMENT 'Поле для тестирования json значений',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для юнит тестов типа smoke(проверяет выполнение основных операций с mysql)';

CREATE TABLE IF NOT EXISTS `file_node`.`file_delete_by_expire_queue` (
	`file_key` VARCHAR(255) NOT NULL COMMENT 'map файла',
	`file_source` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'место хранение файла в приложении',
	`file_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип файла',
	`is_finished` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'завершен ли процесс удаления файла',
	`created_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата загрузки файла',
	`updated_at` INT(11) NOT NULL DEFAULT '0' COMMENT 'дата обновления записи',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'время работы для крона',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во ошибок при удалении файла',
	PRIMARY KEY (`file_key`),
	INDEX `cron_file_cleaner` (`need_work` ASC, `error_count` ASC, `is_finished` ASC) COMMENT 'индекс для крона cleaner')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица-очередь для удаления старых файлов';

-- -----------------------------------------------------
-- endregion
-- -----------------------------------------------------
