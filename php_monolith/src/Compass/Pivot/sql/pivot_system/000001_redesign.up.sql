USE `pivot_system`;

CREATE TABLE IF NOT EXISTS `pivot_system`.`antispam_company` (
	`company_id` BIGINT(20) NOT NULL COMMENT 'id компании',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и company_id',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`company_id`, `key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по company_id';

CREATE TABLE IF NOT EXISTS `pivot_system`.`antispam_ip` (
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'ip адрес',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ для блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и ip',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`ip_address`, `key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по ip';

CREATE TABLE IF NOT EXISTS `pivot_system`.`antispam_phone` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'hash номера телефона',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и телефона',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`phone_number_hash`, `key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по номеру телефона';

CREATE TABLE IF NOT EXISTS `pivot_system`.`antispam_user` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и user_id',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`user_id`, `key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для блокировок по user_id';

CREATE TABLE IF NOT EXISTS `pivot_system`.`auto_increment` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ - название поля',
	`value` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'значение',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для ручного автоинкремента в других таблицах';

CREATE TABLE IF NOT EXISTS `pivot_system`.`datastore` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица с системными значениями для фреймворка\nнужна для работы крона general.php, а также по необходимости в нее могут писать другие модули';

CREATE TABLE IF NOT EXISTS `pivot_system`.`phphooker_queue` (
	`task_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'уникальный идентификатор задачи',
	`task_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип задачи определяемый константой',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда необходимо выполнить задачу',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток исполнения задачи кроном',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания создания задачи',
	`task_global_key` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'идентификатор группы задач',
	`params` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'параметры для исполнения задачи',
	PRIMARY KEY (`task_id`),
	INDEX `task_global_key` (`task_global_key` ASC)  COMMENT 'индекс для получения группы задач',
	INDEX `need_work` (`need_work` ASC) COMMENT 'индекс для получения задач кроном')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица с очередью задач на исполнение phphooker';

CREATE TABLE IF NOT EXISTS `pivot_system`.`default_file_list` (
	`dictionary_key` VARCHAR(255) NOT NULL COMMENT 'словарный ключ файла',
	`file_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ файла',
	`file_hash` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'хеш файла',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительная информация о файле',
	PRIMARY KEY (`dictionary_key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'сервесная таблица, для хранения информации о дефолтных файлах проекта';

CREATE TABLE IF NOT EXISTS `pivot_system`.`unit_test` (
	`key` VARCHAR(32) NOT NULL DEFAULT '' COMMENT 'Идентификатор',
	`int_row` INT(11) NOT NULL DEFAULT 0 COMMENT 'поле для тестирования значений типа int',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'Поле для тестирования json значений',
	PRIMARY KEY (`key`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица для юнит тестов типа smoke(проверяет выполнение основных операций с mysql)';