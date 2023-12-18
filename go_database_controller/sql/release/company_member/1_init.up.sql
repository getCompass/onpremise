/* @formatter:off */

CREATE TABLE IF NOT EXISTS `usercard_achievement_list` (
  `achievement_id` int NOT NULL AUTO_INCREMENT COMMENT 'id достижения',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя для кого достижение',
  `creator_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя создателя записи',
  `type` tinyint NOT NULL DEFAULT 0 COMMENT 'тип достижения',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - достижение удалено или нет (1 - удалено, 0 - нет)',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  `header_text` varchar(255) NOT NULL DEFAULT '' COMMENT 'заголовок достижения',
  `description_text` varchar(10000) NOT NULL DEFAULT '' COMMENT 'текст описания достижению',
  `data` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура достижения',
  PRIMARY KEY (`achievement_id`),
  KEY `get_by_user_id_is_deleted` (`user_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для раздела достижений пользователей';

CREATE TABLE IF NOT EXISTS `usercard_dynamic` (
  `user_id` bigint NOT NULL COMMENT 'идентификатор пользователя',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  `data` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура dynamic-данных',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для записей dynamic-данных пользователя карточки';

CREATE TABLE IF NOT EXISTS `usercard_exactingness_list` (
  `exactingness_id` int NOT NULL AUTO_INCREMENT COMMENT 'идентификатор записи требовательности',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя кому выдана требовательность',
  `creator_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя создателя записи',
  `type` tinyint NOT NULL DEFAULT 0 COMMENT 'тип требовательности',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - требовательность удалена или нет (1 - удалена, 0 - нет)',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  `data` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура данных требовательности',
  PRIMARY KEY (`exactingness_id`),
  KEY `get_by_creator_user_and_is_deleted_and_created_at` (`creator_user_id`,`is_deleted`,`created_at`),
  KEY `get_by_user_id_is_deleted` (`user_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для хранения сущностей требовательностей пользователя';

CREATE TABLE IF NOT EXISTS `usercard_loyalty_list` (
  `loyalty_id` int NOT NULL AUTO_INCREMENT COMMENT 'id оценки вовлеченности',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя для кого оценка',
  `creator_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя создателя записи',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - лояльность удалена или нет (1 - удалена, 0 - нет)',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  `comment_text` varchar(10000) NOT NULL DEFAULT '' COMMENT 'текст описания к оценке вовлеченности',
  `data` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура данных оценки',
  PRIMARY KEY (`loyalty_id`),
  KEY `get_by_user_id_is_deleted` (`user_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для раздела оценок вовлеченности';

CREATE TABLE IF NOT EXISTS `usercard_month_plan_list` (
  `row_id` int NOT NULL AUTO_INCREMENT COMMENT 'идентификатор записи',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, для кого план',
  `type` int NOT NULL DEFAULT 0 COMMENT 'тип сущности плана на месяц (респект, требовательность, etc)',
  `plan_value` int NOT NULL DEFAULT 0 COMMENT 'значение плана на месяц',
  `user_value` int NOT NULL DEFAULT 0 COMMENT 'набранное пользователем за месяц значение',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания плана (всегда = начало месяца monthStartOnGreenwich)',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  PRIMARY KEY (`row_id`),
  UNIQUE KEY `get_by_user_and_type_and_created_at` (`user_id`,`type`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для записей планов на месяц карточки пользователя';

CREATE TABLE IF NOT EXISTS `usercard_respect_list` (
  `respect_id` int NOT NULL AUTO_INCREMENT COMMENT 'идентификатор записи респекта',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя кому выдан респект',
  `creator_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя создателя записи',
  `type` tinyint NOT NULL DEFAULT 0 COMMENT 'тип респекта',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - респект удален или нет (1 - удален, 0 - нет)',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  `respect_text` varchar(10000) NOT NULL DEFAULT '' COMMENT 'текст респекта',
  `data` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура данных респекта',
  PRIMARY KEY (`respect_id`),
  KEY `get_by_user_id_is_deleted` (`user_id`,`is_deleted`),
  KEY `get_by_creator_user_and_is_deleted_and_created_at` (`creator_user_id`,`is_deleted`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для раздела респектов пользователя';

CREATE TABLE IF NOT EXISTS `usercard_sprint_list` (
  `sprint_id` int NOT NULL AUTO_INCREMENT COMMENT 'id записи',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя',
  `creator_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя создателя записи',
  `is_success` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'bool значение о закрытие спринта',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - спринт удален или нет (1 - удален, 0 - нет)',
  `started_at` int NOT NULL DEFAULT 0 COMMENT 'время начала спринта',
  `end_at` int NOT NULL DEFAULT 0 COMMENT 'время конца спринта',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'время создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'время обновления записи',
  `header_text` varchar(255) NOT NULL DEFAULT '' COMMENT 'заголовок спринта',
  `description_text` varchar(10000) NOT NULL DEFAULT '' COMMENT 'описание спринта',
  `data` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура спринта',
  PRIMARY KEY (`sprint_id`),
  KEY `get_by_user_id_and_is_deleted` (`user_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для спринтов пользователей';

CREATE TABLE IF NOT EXISTS `usercard_worked_hour_list` (
  `worked_hour_id` int NOT NULL AUTO_INCREMENT COMMENT 'id рабочих часов',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя кто зафиксировал рабочие часы',
  `day_start_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка начала дня, за которыми закреплены рабочие часы',
  `type` tinyint NOT NULL DEFAULT 0 COMMENT 'тип рабочих часов',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 - рабочие часы удалены или нет (1 - удалены, 0 - нет)',
  `value_1000` int NOT NULL DEFAULT 0 COMMENT 'значение рабочих часов (формат: float*1000)',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  `data` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура данных рабочих часов',
  PRIMARY KEY (`worked_hour_id`),
  UNIQUE KEY `get_by_user_id_day_start_at` (`user_id`,`day_start_at`) COMMENT 'unique-индекс для создания одной записи рабочих часов за день',
  KEY `get_by_user_id_is_deleted` (`user_id`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для рабочих часов карточки пользователя';

CREATE TABLE IF NOT EXISTS `usercard_member_rel` (
  `row_id` int NOT NULL AUTO_INCREMENT COMMENT 'идентификатор строки',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
  `role` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'роль пользователя',
  `recipient_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя-получателя роли',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 является ли запись удаленной (1 - удалена, 0 - НЕ удалена)',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
  PRIMARY KEY (`row_id`),
  UNIQUE KEY `user_id_and_recipient_user_id` (`user_id`,`recipient_user_id`),
  KEY `get_by_user_id_role_is_deleted` (`user_id`,`role`,`is_deleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица, в которой хранятся пользователи и их роли по отношению друг к другу';

CREATE TABLE IF NOT EXISTS `security_list` (
  `user_id` bigint NOT NULL COMMENT 'id пользователя',
  `is_pin_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'обязателен ли пин',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
  `last_enter_pin_at` int NOT NULL DEFAULT 0 COMMENT 'дата времени последнего ввода пин кода',
  `pin_hash_version` int NOT NULL DEFAULT 0 COMMENT 'версия пина',
  `pin_hash` varchar(40) NOT NULL DEFAULT '' COMMENT 'hash пина',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для хранения параметров безопасности пользователя';

CREATE TABLE IF NOT EXISTS `security_pin_enter_history` (
  `try_enter_id` bigint NOT NULL AUTO_INCREMENT COMMENT 'id ввода пинкода',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT 'статус правилен ли пин код',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `enter_pin_hash_version` int NOT NULL DEFAULT 0 COMMENT 'версия пина',
  `enter_pin_hash` varchar(40) NOT NULL DEFAULT '' COMMENT 'hash введенного пин кода',
  `user_company_session_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя в компанию',
  PRIMARY KEY (`try_enter_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для записи ввода пин кодов';

CREATE TABLE IF NOT EXISTS `security_pin_change_history` (
  `user_id` bigint NOT NULL COMMENT 'id пользователя',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `previous_pin_hash_version` int NOT NULL DEFAULT 0 COMMENT 'версия прошлого пина',
  `new_pin_hash_version` int NOT NULL DEFAULT 0 COMMENT 'версия нового пина',
  `previous_pin_hash` varchar(40) NOT NULL DEFAULT '' COMMENT 'hash прошлого пина',
  `new_pin_hash` varchar(40) NOT NULL DEFAULT '' COMMENT 'hash нового пина',
  `ua_hash` varchar(64) NOT NULL DEFAULT '' COMMENT 'hash user agent клиента',
  `ip_address` varchar(45) NOT NULL DEFAULT '' COMMENT 'ip address',
  PRIMARY KEY (`user_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для записи истории смены pin';

CREATE TABLE IF NOT EXISTS `security_pin_restore_story` (
  `restore_id` int NOT NULL COMMENT 'id восстановления',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT 'статус смены пинкода',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
  `need_confirm_at` int NOT NULL DEFAULT 0 COMMENT 'время когда нужно подтвердить изменение пина',
  `ua_hash` varchar(64) NOT NULL DEFAULT '' COMMENT 'hash user agent клиента',
  `ip_address` varchar(45) NOT NULL DEFAULT '' COMMENT 'ip address',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra записи',
  PRIMARY KEY (`restore_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для записи истории восстановлений пинкода пользователя';

CREATE TABLE IF NOT EXISTS `security_pin_confirm_story` (
  `confirm_key` varchar(255) NOT NULL,
  `user_id` bigint NOT NULL DEFAULT 0,
  `status` tinyint(4) NOT NULL DEFAULT '0',
  `created_at` int NOT NULL DEFAULT 0,
  `updated_at` int NOT NULL DEFAULT 0,
  `expires_at` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`confirm_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для списка совершенных аутентификаций';
