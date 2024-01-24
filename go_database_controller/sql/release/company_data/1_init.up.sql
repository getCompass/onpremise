use
company_data;

/* @formatter:off */

CREATE TABLE IF NOT EXISTS `member_list` (
  `user_id` bigint NOT NULL COMMENT 'id пользователя',
  `role` int NOT NULL DEFAULT 0 COMMENT 'роль',
  `npc_type` int NOT NULL DEFAULT 0 COMMENT 'npc_type пользователя',
  `permissions` int NOT NULL DEFAULT 0 COMMENT 'права пользователя',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата изменения записи',
  `company_joined_at` int NOT NULL DEFAULT 0 COMMENT 'дата вступления в компанию',
  `full_name_updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата изменения имени',
  `mbti_type` varchar(10) NOT NULL DEFAULT '' COMMENT 'mbti type пользователя',
  `full_name` varchar(255) NOT NULL DEFAULT '' COMMENT 'имя пользователя',
  `short_description` varchar(255) NOT NULL DEFAULT '' COMMENT 'короткое описание пользователя',
  `avatar_file_key` varchar(255) NOT NULL DEFAULT '' COMMENT 'аватар пользователя',
  `comment` varchar(400) NOT NULL DEFAULT '' COMMENT 'комментарий к пользователю',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra записи',
  PRIMARY KEY (`user_id`),
  KEY `get_by_npc_type` (`npc_type`, `company_joined_at`),
  KEY `mbti_type_by_role` (`mbti_type` ASC, `role` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='список пользователей компании';

CREATE TABLE IF NOT EXISTS `member_notification_list` (
  `user_id` bigint NOT NULL COMMENT 'идентификатор пользователя',
  `snoozed_until` int NOT NULL DEFAULT 0 COMMENT 'до какого времени пуши отключены',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'время создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'время изменения записи',
  `token` varchar(40) NOT NULL DEFAULT '' COMMENT 'хэш токена пользователя',
  `device_list` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список девайсов',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra данные',
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для хранения токенов пользователя для компании для получения пушей';

CREATE TABLE IF NOT EXISTS `session_active_list` (
  `session_uniq` varchar(255) NOT NULL COMMENT 'уникальный идентификатор сессии',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя',
  `user_company_session_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя в компанию',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
  `login_at` int NOT NULL DEFAULT 0 COMMENT 'дата логина в сессию',
  `ip_address` varchar(45) NOT NULL DEFAULT '' COMMENT 'ip address',
  `user_agent` varchar(255) NOT NULL DEFAULT '' COMMENT 'user agent клиента',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra записи',
  PRIMARY KEY (`session_uniq`),
  UNIQUE KEY `user_company_session_token` (`user_company_session_token`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для всех активных сессий';

CREATE TABLE IF NOT EXISTS `session_history_list` (
  `session_uniq` varchar(255) NOT NULL COMMENT 'уникальный идентификатор сессии',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя',
  `user_company_session_token` varchar(255) NOT NULL DEFAULT '' COMMENT 'токен пользователя в компанию',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT 'статус сессии в истории',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `login_at` int NOT NULL DEFAULT 0 COMMENT 'дата логина сессии',
  `logout_at` int NOT NULL DEFAULT 0 COMMENT 'дата разлогина сессии',
  `ip_address` varchar(45) NOT NULL DEFAULT '' COMMENT 'ip address',
  `user_agent` varchar(255) NOT NULL DEFAULT '' COMMENT 'user agent клиента',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra записи',
  PRIMARY KEY (`session_uniq`),
  KEY `user_id` (`user_id`),
  KEY `user_company_session_token` (`user_company_session_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для записи истории сессий';

CREATE TABLE IF NOT EXISTS `entry_list` (
  `entry_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'id входа в компанию',
  `entry_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип входа в компанию. 1 - вошел по ссылке',
  `user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя который вступает в компанию',
  `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
PRIMARY KEY (`entry_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `entry_invite_link_list` (
  `entry_id` INT(11) NOT NULL COMMENT 'id входа в компанию',
  `invite_link_uniq` VARCHAR(12) NOT NULL DEFAULT '' COMMENT 'идентификатор инвайта-ссылки',
  `inviter_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор приглашающего пользователя',
  `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
PRIMARY KEY (`entry_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `invite_link_list` (
  `invite_link_uniq` VARCHAR(12) NOT NULL COMMENT 'идентификатор ссылки-инвайта',
  `is_postmoderation` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0/1 настройки постмодерации приглашения (0 - не требует подтверждения; 1 - должен подтвердить руководитель)',
  `status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус инвайта',
  `type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип инвайта',
  `can_use_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сколько раз можно использовать приглашение',
  `expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда приглашение станет неактивным',
  `creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'создатель инвайта',
  `created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания инвайта',
  `updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления инвайта',
  PRIMARY KEY (`invite_link_uniq`),
  INDEX `get_by_type_and_status` (`type` ASC, `status` ASC),
  INDEX `get_created_by_user_id_and_status` (`creator_user_id`, `status`, `created_at`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;

CREATE TABLE IF NOT EXISTS `hiring_request` (
  `hiring_request_id` int NOT NULL AUTO_INCREMENT COMMENT 'id заявки',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT 'статус заявки (ожидает, принята, отклонена)',
  `invite_link_uniq` varchar(12) NOT NULL DEFAULT '' COMMENT 'id инвайта',
  `entry_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id входа в компанию',
  `hired_by_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id создателя заявки',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания заявки',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата обновления заявки',
  `candidate_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id приглашенного пользователя',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'данные для автоматического добавления в диалоги (для каждой заявки набор уникальный и может быть отредактирован, данные лежат прямо в заявке, а не ссылкой на пресет)',
  PRIMARY KEY (`hiring_request_id`),
  KEY `entry_id` (`entry_id`),
  KEY `candidate_user_id` (`candidate_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица заявок на найм';

CREATE TABLE IF NOT EXISTS `hiring_conversation_preset` (
  `hiring_conversation_preset_id` int NOT NULL AUTO_INCREMENT COMMENT 'id пресета',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT 'статус пресета (активен, недоступен)',
  `creator_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id создателя пресета',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания пресета',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата обновления пресета',
  `title` varchar(80) NOT NULL DEFAULT '' COMMENT 'идентификатор',
  `conversation_list` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список групп, которые входят в пресет',
  PRIMARY KEY (`hiring_conversation_preset_id`),
  KEY `user_status` (`creator_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица пресетов групп';

CREATE TABLE IF NOT EXISTS `dismissal_request` (
  `dismissal_request_id` int NOT NULL AUTO_INCREMENT COMMENT 'id заявки',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT 'статус заявки (ожидает, принята, отклонена)',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания заявки',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата обновления заявки',
  `creator_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя инициирующего увольнение',
  `dismissal_user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id увольняемого пользователя',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'данные для автоматического добавления в диалоги (для каждой заявки набор уникальный и может быть отредактирован, данные лежат прямо в заявке, а не ссылкой на пресет)',
  PRIMARY KEY (`dismissal_request_id`),
  KEY `dismissal_user_id_by_status` (`dismissal_user_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица заявок на увольнение';

CREATE TABLE IF NOT EXISTS `company_config` (
	`key` VARCHAR(255) NOT NULL COMMENT 'ключ настройки',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата изменения записи',
	`value` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'значение настройки',
	PRIMARY KEY (`key`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COMMENT 'таблица для хранения настроек компании';

INSERT INTO `company_config` VALUES('member_count', 0, 0, '{"value": 0}');
INSERT INTO `company_config` VALUES('is_pin_required', 0, 0, '{"value": 0}');

CREATE TABLE IF NOT EXISTS `company_dynamic` (
  `key` varchar(255) NOT NULL COMMENT 'ключ настройки',
  `value` int NOT NULL DEFAULT 0 COMMENT 'значение',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'дата изменения записи',
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для хранения динамических данных компании';

CREATE TABLE IF NOT EXISTS `rating_member_hour_list` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'Идентификатор пользователя для которого собиралась статистика',
	`hour_start` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'час записи',
	`is_disabled_alias` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг, показывающий что пользователь активен в системе',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Дата последнего обновления записи',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json с количеством эвентов',
	PRIMARY KEY (`user_id`, `hour_start`),
	INDEX `hour_start` (`hour_start` ASC)  COMMENT 'Индекс для выборки по часу')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'статистика по пользователю за каждый час';

CREATE TABLE IF NOT EXISTS `rating_member_day_list` (
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'Пользователь для которого собирался рейтинг',
	`day_start` INT(11) NOT NULL DEFAULT 0 COMMENT 'День, за который собирался рейтинг',
	`is_disabled_alias` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг, показывающий что пользователь активен в системе',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Временная метка последнего обновления записи',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json с дополнительными данными',
	PRIMARY KEY (`user_id`, `day_start`),
	INDEX `day_start` (`day_start` ASC)  COMMENT 'Индекс для выборки по дням')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица с рейтингом по пользователю за день';

CREATE TABLE IF NOT EXISTS `rating_day_list` (
	`day_start` INT(11) NOT NULL COMMENT 'День, за который собирался рейтинг',
	`general_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Общее количество рейтинга',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Временная метка последнего обновления записи',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json с дополнительными данными',
	PRIMARY KEY (`day_start`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'таблица с рейтингом за день';

CREATE TABLE IF NOT EXISTS `file_list` (
  `meta_id` int NOT NULL COMMENT 'мета идентификатор файла - auto_increment',
  `year` INT(11) NOT NULL COMMENT 'год с записью',
  `month` INT(11) NOT NULL COMMENT 'месяц с записью',
  `file_type` tinyint NOT NULL DEFAULT 0 COMMENT 'тип файла',
  `file_source` INT(11) NOT NULL DEFAULT 0 COMMENT 'место хранение файла в приложении',
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'флаг указывающий удален ли файл 0 - не удален 1-удален',
  `node_id` int NOT NULL DEFAULT 0 COMMENT 'нода на которой хранится файл',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'дата записи файла',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
  `size_kb` int NOT NULL DEFAULT 0 COMMENT 'размер файла в килобайтах',
  `user_id` bigint NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
  `file_hash` varchar(40) NOT NULL DEFAULT '' COMMENT 'Хэш файла',
  `mime_type` varchar(255) NOT NULL DEFAULT '' COMMENT 'mime-type файла',
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '' COMMENT 'название файла',
  `file_extension` varchar(255) NOT NULL DEFAULT '' COMMENT 'расширение файла',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra файла в json структуре',
  PRIMARY KEY (`meta_id`, `year`, `month`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='таблица в которой хранятся постоянные файлы';

CREATE TABLE IF NOT EXISTS `preview_list` (
	`preview_hash` VARCHAR(255) NOT NULL COMMENT 'sha1 хэш от ссылки',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Удалена ли ссылка: да/нет',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`data` JSOn NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'из этого поля собирается UrlPreview',
	PRIMARY KEY (`preview_hash`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для превью ссылок';

CREATE TABLE IF NOT EXISTS `exit_list` (
	`exit_task_id` INT(11) NOT NULL COMMENT 'id задачи на увольнение',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя которого увольняем',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус задачи',
	`step` TINYINT(4) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'текущий шаг работы',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания задачи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время обновления задачи',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'массив системных данных',
	PRIMARY KEY (`exit_task_id`),
	INDEX `STATUS` (`status` ASC))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8;