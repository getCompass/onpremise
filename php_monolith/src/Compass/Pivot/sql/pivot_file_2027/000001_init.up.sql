USE `pivot_file_2027`;

CREATE TABLE IF NOT EXISTS `file_list_1` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_2` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_3` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_4` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_5` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_6` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_7` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_8` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_9` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_10` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_11` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `file_list_12` (
	`meta_id` INT(11) NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг указывающий удален ли файл 0 - не удаден 1-удален',
	`is_cdn` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'использует ли загруженный файл cdn',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`size_kb` INT(11) NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`file_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
	`mime_type` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
	`file_name` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_general_ci' NOT NULL DEFAULT '' COMMENT 'название файла',
	`file_extension` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
	`content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации',
	PRIMARY KEY (`meta_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица в которой хранятся постоянные файлы';