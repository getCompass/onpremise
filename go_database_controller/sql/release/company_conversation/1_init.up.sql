/* @formatter:off */

CREATE TABLE IF NOT EXISTS `conversation_meta` (
	`meta_id` INT(11) NOT NULL COMMENT 'уникальный id диалога в рамках таблицы',
	`year` INT(11) NOT NULL COMMENT 'год с записью',
	`allow_status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'можно ли писать в диалог:\n1 - да, 2 - нужна проверка, 8 - бан, нет',
	`type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип диалога (single/group)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'user_id пользователя создавшего диалог',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map аватарки группового диалога',
	`conversation_name` VARCHAR (80) NOT NULL DEFAULT '' COMMENT 'название диалога',
	`users` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json массив с пользователями состоящими в диалоге',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	PRIMARY KEY (`meta_id`, `year`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='основная, не изменяющаяся (или редко изменяющаяся) информация о диалоге';

CREATE TABLE IF NOT EXISTS `conversation_file` (
	`file_uuid` VARCHAR(40) NOT NULL COMMENT 'уникальный идентификатор записи',
	`row_id` INT(11) NOT NULL AUTO_INCREMENT,
	`conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map диалога',
	`file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map файла',
	`file_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип файла',
	`parent_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип родительского сообщения (из диалога, треда)',
	`conversation_message_created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания сообщения в диалоге',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0 - не удален, 1 - удален',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя загрузившего файл',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
	`parent_message_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map родительского сообщения',
	`conversation_message_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'поле хранит map сообщения с которым файл добавился в диалог',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле хранит список идентификаторов пользователей скрывших файл',
	PRIMARY KEY (`file_uuid`),
	INDEX `get_by_row_id` (`row_id` ASC) COMMENT 'индекс для получения записей по row_id (бывший primary)',
	INDEX `get_files` (`conversation_map` ASC,`conversation_message_created_at` ASC,`file_type` ASC,`is_deleted` ASC) COMMENT 'индекс для выборки всех файлов',
	INDEX `conversation_message_map` (`conversation_message_map` ASC) COMMENT 'индекс для выборки своих файлов'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица файлов загруженных в диалогах';

CREATE TABLE IF NOT EXISTS `conversation_dynamic` (
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map диалога',
	`is_locked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'закрыт ли диалог для добавления сообщений',
	`last_block_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id последнего блока с сообщениями',
	`start_block_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id первого доступного блока в диалоге',
	`total_message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'счетчик сообщений в диалоге',
	`file_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'поле для хранения количества файлов в conversation',
	`image_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'поле хранит количество изображений в диалоге',
	`video_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'поле хранит количество видео в диалоге',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`messages_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления метки сообщений',
	`reactions_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления метки реакций',
	`threads_updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновелния метки тредов',
	`user_mute_info` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'информация о муте диалога участниками',
	`user_clear_info` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'информация об очистке диалога участниками',
	`user_file_clear_info` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'количество скрытых для пользователя файлов и изображений',
	`conversation_clear_info` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'информация об очистке диалога у участников',
	PRIMARY KEY (`conversation_map`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с часто изменяющейся информацией о диалоге';

CREATE TABLE IF NOT EXISTS `conversation_invite_list` (
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map диалога',
	`invite_map` VARCHAR(255) NOT NULL COMMENT 'map инвайта',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'статус инвайта',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id получателя инвайта',
	`sender_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id отправителя инвайта',
	PRIMARY KEY (`conversation_map`,`invite_map`),
	INDEX `conversation_map` (`conversation_map` ASC),
	INDEX `user_id` (`user_id` ASC),
	INDEX `sender_user_id` (`sender_user_id` ASC),
	INDEX `status` (`status` ASC)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `message_block_1` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_2` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_3` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_4` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_5` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_6` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_7` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_8` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_9` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_10` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_11` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_12` (
	`conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_reaction_list` (
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор диалога',
	`block_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор блока',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	`reaction_data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле содержит JSON структуру с поставленными реакциями на сообщения блока',
	PRIMARY KEY (`conversation_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='реакции + их количество для блока сообщений';

CREATE TABLE IF NOT EXISTS `message_user_hidden_rel` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'map сообщения',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	PRIMARY KEY (`user_id`,`message_map`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для скрытых пользователем сообщений';

CREATE TABLE IF NOT EXISTS `message_repost_thread_rel` (
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map диалога, откуда совершен репост',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'map сообщения репоста в треде получателе',
	`receiver_thread_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map треда, куда был совершен репост',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, совершивший репост',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0 - сообщение с репостом в диалоге получателе не удалено; 1 - сообщение с репостом в диалоге получателе не удалено',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда был совершен репост',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда была обновлена запись с репостом',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда было удалено сообщение с репостом',
	PRIMARY KEY (`conversation_map`,`message_map`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с историей совершенных репостов из диалога conversation_map в тред receiver_thread_map';

CREATE TABLE IF NOT EXISTS `message_report_history` (
	`report_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'id репорта',
	`message_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'карта сообщения',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, отправившего репорт',
	`reason` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'причина репорта',
	PRIMARY KEY (`report_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='история репортов на сообщения';

CREATE TABLE IF NOT EXISTS `invite_list` (
	`meta_id` INT(11) NOT NULL COMMENT 'уникальный id инвайта',
	`year` INT(11) NOT NULL COMMENT 'год записи',
	`type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип инвайта (пока только single)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	PRIMARY KEY (`meta_id`, `year`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='основная, не изменяющаяся информация об инвайте';

CREATE TABLE IF NOT EXISTS `invite_group_via_single` (
	`invite_map` VARCHAR(255) NOT NULL COMMENT 'map инвайта',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'текущий статус инвайта',
	`inactive_reason` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'причина неактивного статуса приглашения',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'user_id пользователя, получившего приглашение',
	`sender_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, отправившего приглашение',
	`conversation_name` VARCHAR(80) NOT NULL DEFAULT '' COMMENT 'название диалога',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map аватарки группового диалога',
	`group_conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map диалога, в который приглашен пользователь',
	`single_conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map сингл диалога, в который отправлено сообщение о приглашении',
	PRIMARY KEY (`invite_map`),
	UNIQUE INDEX `user_conversation_sender_UNIQUE` (`user_id` ASC,`sender_user_id` ASC,`group_conversation_map` ASC)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица с single инвайтами';

CREATE TABLE IF NOT EXISTS `user_left_menu` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map диалога',
	`is_favorite` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'диалог в избранном (bool)',
	`is_mentioned` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'упомянули в диалоге (bool)',
	`is_muted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'диалог в перманентном муте (bool)',
	`muted_until` INT(11) NOT NULL DEFAULT 0 COMMENT 'время до которого диалог будет в муте',
	`is_hidden` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'диалог скрыт (bool)',
	`is_leaved` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'покинул ли пользователь групповой диалог (bool)',
	`allow_status_alias` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'поле показывающее отношение собеседников',
	`leave_reason` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'по какой причине пользователь покинул диалог',
	`role` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'роль пользователя в диалоге',
	`type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип диалога (single/group)',
	`unread_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество непрочитанных сообщений',
	`is_have_notice` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'требуется ли красная точка для этого диалога',
	`member_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество участников диалога',
	`version` INT(11) NOT NULL DEFAULT 0 COMMENT 'версия левого меню',
	`clear_until` INT(11) NOT NULL DEFAULT 0 COMMENT 'время, до которого диалог был очищен',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`opponent_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор оппонента в single диалоге',
	`conversation_name` VARCHAR(80) NOT NULL DEFAULT '' COMMENT 'имя диалога',
	`avatar_file_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map аватарки диалога',
	`last_read_message_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор последнего прочтенного сообщения',
	`last_message` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'JSON объект последнего сообщения в диалоге',
	PRIMARY KEY (`user_id`,`conversation_map`),
	INDEX `get_managed` (`user_id` ASC,`role` ASC,`updated_at` ASC) COMMENT 'Индекс для выборки переписок в которой пользователь является админом или создателем ',
	INDEX `get_opponents` (`user_id` ASC,`opponent_user_id` ASC,`is_hidden` ASC) COMMENT 'Индекс для выборки диалогов с определенными собеседниками без сортировки',
	INDEX `get_allowed` (`user_id` ASC,`is_hidden` ASC,`allow_status_alias` ASC,`type` ASC,`is_favorite` DESC,`updated_at` DESC) COMMENT 'Индекс для выборки диалогов с собеседниками с которыми можно создать группу',
	INDEX `get_left_menu` (`user_id` ASC, `is_hidden` ASC, `is_favorite` ASC, `is_mentioned` ASC, `updated_at` ASC),
	INDEX `get_unread_menu` (`user_id` ASC, `is_hidden` ASC, `is_have_notice` ASC, `is_mentioned` ASC, `updated_at` ASC),
	INDEX `get_versioned_menu` (`user_id` ASC, `version` ASC)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица с информацией пользователя по всем его диалогам';

CREATE TABLE IF NOT EXISTS `user_inbox` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`message_unread_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'общее количество непрочитанных сообщений в чатах у пользователя',
	`conversation_unread_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'общее количество непрочитанных чатов у пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	PRIMARY KEY (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с информацией о непрочитанных пользователя';

CREATE TABLE IF NOT EXISTS `user_single_uniq` (
	`user1_id` BIGINT(20) NOT NULL COMMENT 'id пользователя, создавшего диалога (ключ для шардинга)',
	`user2_id` BIGINT(20) NOT NULL COMMENT 'id оппонента этого пользователя',
	`conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map диалога',
	PRIMARY KEY (`user1_id`,`user2_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для определения map диалога между двумя пользователями зная их user_id';

CREATE TABLE IF NOT EXISTS `user_invite_rel` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя, получившего приглашение',
	`invite_map` VARCHAR(255) NOT NULL COMMENT 'map инвайта',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус инвайта',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'unix метка времени добавления записи в таблицу',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'unix метка времени обновления записи в таблице',
	`sender_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, отправившего приглашение',
	`group_conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map группового диалога',
	PRIMARY KEY (`user_id`,`invite_map`),
	INDEX `get_by_user_id_and_status` (`user_id` ASC,`status` ASC) COMMENT 'индекс для получения инвайтов пользователя'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица с информацией пользователя по инвайтам';

CREATE TABLE IF NOT EXISTS `user_dynamic` (
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map диалога к которому относится счетчик',
	`user_id` BIGINT(20) NOT NULL COMMENT 'map пользователя к которому относится счетчик',
	`count_sender_active_invite` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'количество активных инвайтов пользователя в данный момент',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'время последнего обновления записи',
	PRIMARY KEY (`conversation_map`,`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица для счетчика активных приглашений отправленных одним пользователем в одну группу';

CREATE TABLE IF NOT EXISTS `member_conversation_type_rel` (
	`row_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор строки',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя',
	`type` INT(11) NOT NULL DEFAULT 0 COMMENT 'тип диалога',
	`conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'ключ диалога пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	PRIMARY KEY (`row_id`),
	INDEX `get_by_user_id_and_type` (`user_id`,`type`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица для хранения списка ключей личных диалогов пользователя (например, личный чат Heroes)';

CREATE TABLE IF NOT EXISTS `message_repost_conversation_rel` (
	`conversation_map` VARCHAR(255) NOT NULL COMMENT 'map диалога, откуда совершен репост',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'map сообщения репоста в диалоге получателе',
	`reciever_conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map диалога, куда был совершен репост',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, совершивший репост',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0 - сообщение с репостом в диалоге получателе не удалено; 1 - сообщение с репостом в диалоге получателе не удалено',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда был совершен репост',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда была обнавлена запись с репостом',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда было удалено сообщение с репостом',
	PRIMARY KEY (`conversation_map`,`message_map`),
	INDEX `get_existing_repost` (`conversation_map` ASC,`is_deleted` ASC)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с историей совершенных репостов из диалога conversation_map в reciever_conversation_map';

CREATE TABLE IF NOT EXISTS `message_thread_rel` (
  `conversation_map` varchar(255) NOT NULL COMMENT 'map диалога к которому прикреплен блок',
  `message_map` varchar(255) NOT NULL COMMENT 'map сообщение к которому прикреплен тред',
  `thread_map` varchar(255) NOT NULL DEFAULT '' COMMENT 'map треда',
  `block_id` bigint NOT NULL DEFAULT '0' COMMENT 'id блока, в котором лежит тред',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'extra записи',
  PRIMARY KEY (`conversation_map`,`message_map`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COMMENT='Таблица для получения тредов диалога по блоку сообщений диалога';