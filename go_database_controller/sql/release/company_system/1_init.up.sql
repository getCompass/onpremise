/* @formatter:off */

CREATE TABLE IF NOT EXISTS `migration_release_database_list` (
	`full_database_name` VARCHAR(255) NOT NULL COMMENT 'полное имя базы',
	`database_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя базы',
	`is_completed` INT(11) NOT NULL DEFAULT 0 COMMENT 'завершена ли миграция',
	`current_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'текущая версия базы',
	`previous_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'предыдущая версия базы',
	`expected_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'ожидаемая версия базы',
	`highest_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'самая высокая версия базы',
	`last_migrated_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'последний тип миграции',
	`last_migrated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'таймштамп последней миграции',
	`last_migrated_file` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'последний файл использованный при миграции',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	PRIMARY KEY (`full_database_name`)
	) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для миграции баз';

CREATE TABLE IF NOT EXISTS `migration_cleaning_database_list` (
	`full_database_name` VARCHAR(255) NOT NULL COMMENT 'полное имя базы',
	`database_name` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'имя базы',
	`is_completed` INT(11) NOT NULL DEFAULT 0 COMMENT 'завершена ли миграция',
	`current_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'текущая версия базы',
	`previous_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'предыдущая версия базы',
	`expected_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'ожидаемая версия базы',
	`highest_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'самая высокая версия базы',
	`last_migrated_type` INT(11) NOT NULL DEFAULT 0 COMMENT 'последний тип миграции',
	`last_migrated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'таймштамп последней миграции',
	`last_migrated_file` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'последний файл использованный при миграции',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	PRIMARY KEY (`full_database_name`)
	) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для чистки баз от легаси';

CREATE TABLE IF NOT EXISTS `observer_member` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя, за которым наблюдает обсервер',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда пользователя взять в работу',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`user_id`),
	INDEX `need_work` (`need_work` ASC)  COMMENT 'индекс для получения задач кроном'
        ) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица со списком пользователей, за которыми наблюдает обсервер';

CREATE TABLE IF NOT EXISTS `antispam_ip` (
	`ip_address` VARCHAR(45) NOT NULL COMMENT 'ip адрес',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ для блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и ip',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`ip_address`,`key`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для блокировок по ip';

CREATE TABLE IF NOT EXISTS `antispam_user` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и user_id',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`user_id`, `key`)
	) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для блокировок по user_id';

CREATE TABLE IF NOT EXISTS `antispam_phone` (
	`phone_number` VARCHAR(60) NOT NULL COMMENT 'номер телефона',
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ блокировки',
	`is_stat_sent` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'отправлена ли статистика для этого ключа и телефона',
	`count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество попыток',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время когда блокировка станет неактуальной',
	PRIMARY KEY (`phone_number`,`key`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для блокировок по номеру телефона';

CREATE TABLE IF NOT EXISTS `auto_increment` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ',
	`value` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'значение',
	PRIMARY KEY (`key`)
	) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для инкремента по ключу';

CREATE TABLE IF NOT EXISTS `datastore` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'произвольный JSON массив',
	PRIMARY KEY (`key`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица с системными значениями для фреймворка\nнужна для работы крона general.php, а также по необходимости в нее могут писать другие модули';

CREATE TABLE IF NOT EXISTS `go_event_subscriber_list` (
  `subscriber` varchar(255) NOT NULL COMMENT 'идентификатор подписчика',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата изменения записи',
  `subscription_list` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список подписок',
  PRIMARY KEY (`subscriber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='таблица для хранения списка подписок для go_event';