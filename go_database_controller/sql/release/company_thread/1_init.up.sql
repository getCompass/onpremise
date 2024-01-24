/* @formatter:off */

CREATE TABLE IF NOT EXISTS `thread_meta` (
	`meta_id` INT(11) NOT NULL COMMENT 'автоинкрементный идентификатор треда',
	`year` INT(11) NOT NULL COMMENT 'год записи',
	`is_private` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'является ли тред приватным',
	`is_mono` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'могут ли в тред писать все пользователи',
	`is_readonly` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0 - тред доступен не только для чтения; 1 - тред доступен только для чтения',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания треда',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в треде',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который инициировал создание треда',
	`users` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'участники треда',
	`source_parent_rel` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'правила обращения к родительской сущности',
	`parent_rel` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'объект с информацией о родительской сущности треда',
	`sender_order` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'порядок постов в треде',
	`last_sender_data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'информация о N последних отправителей сообщений в тред',
	PRIMARY KEY (`meta_id`,`year`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с метой треда';

CREATE TABLE IF NOT EXISTS `thread_follower_list` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда',
	`follower_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'массив с подписчиками треда',
	`unfollower_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'массив с отписавшимися подписчиками треда',
	PRIMARY KEY (`thread_map`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='фолловеры треда, на основе которых происходит рассылка push-уведомлений';

CREATE TABLE IF NOT EXISTS `thread_dynamic` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда',
	`is_locked` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'закрыт ли тред для добавления сообщений',
	`last_block_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id первого доступного блока с сообщениями',
	`start_block_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'id первого блока с сообщениями',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`user_mute_info` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'информация о муте треда участниками',
	`user_hide_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'пользователи, у которых скрыты все сообщение в треде',
	PRIMARY KEY (`thread_map`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='табличка с основной информацией обсуждения';

CREATE TABLE IF NOT EXISTS `message_block_1` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_2` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_3` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_4` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_5` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_6` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_7` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_8` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_9` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_10` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_11` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_12` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда к которому относится блок',
	`block_id` INT(11) NOT NULL COMMENT 'id блока',
	`message_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество сообщений в блоке',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата последнего обновления записи',
	`closed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата закрытия блока (message_count = max)',
	`data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json структура с сообщениями',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE IF NOT EXISTS `message_block_reaction_list` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор треда',
	`block_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор блока',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи',
	`reaction_data` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'поле содержит JSON структуру с поставленными реакциями на сообщения блока',
	PRIMARY KEY (`thread_map`,`block_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='реакции + их количество для блока сообщений';

CREATE TABLE IF NOT EXISTS `message_report_history` (
	`report_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'id репорта',
	`message_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'карта сообщения',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, отправившего репорт',
	`reason` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'причина репорта',
	PRIMARY KEY (`report_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='история репортов на сообщения';

CREATE TABLE IF NOT EXISTS `message_repost_conversation_rel` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда, откуда совершен репост',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'map сообщения репоста в диалоге получателе',
	`reciever_conversation_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map диалога, куда был совершен репост',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, совершивший репост',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0 - сообщение с репостом в диалоге получателе не удалено; 1 - сообщение с репостом в диалоге получателе удалено',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда был совершен репост',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда была обнавлена запись с репостом',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда было удалено сообщение с репостом',
	PRIMARY KEY (`thread_map`,`message_map`),
	INDEX `get_existing_repost` (`thread_map` ASC,`is_deleted` ASC)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с историей совершенных репостов из треда thread_map в reciever_conversation_map';

CREATE TABLE IF NOT EXISTS `message_repost_thread_rel` (
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда, откуда совершен репост',
	`message_map` VARCHAR(255) NOT NULL COMMENT 'map сообщения репоста в треде получателе',
	`receiver_thread_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map треда, куда был совершен репост',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, совершивший репост',
	`is_deleted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0 - сообщение с репостом в диалоге получателе не удалено; 1 - сообщение с репостом в диалоге получателе не удалено',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда был совершен репост',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда была обнавлена запись с репостом',
	`deleted_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка когда было удалено сообщение с репостом',
	PRIMARY KEY (`thread_map`,`message_map`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с историей совершенных репостов из треда thread_map в тред receiver_thread_map';

CREATE TABLE IF NOT EXISTS `user_inbox` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`message_unread_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'общее количество непрочитанных сообщений в тредах у пользователя',
	`thread_unread_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'общее количество непрочитанных тредов у пользователя',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
	PRIMARY KEY (`user_id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица с информацией пользователя, касающейся всех тредов';

CREATE TABLE IF NOT EXISTS `user_thread_menu` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`thread_map` VARCHAR(255) NOT NULL COMMENT 'map треда',
	`source_parent_type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип родителя треда',
	`is_hidden` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'возвращается ли тред в меню (bool)',
	`is_follow` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'подписан ли пользователь на тред',
	`is_muted` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'замьючен ли у пользователя тред',
	`is_favorite` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'тред в избранном (bool)',
	`unread_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество непрочитанных сообщений в треде',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'последнее обновление записи',
	`source_parent_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map родителя',
	`last_read_message_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map последнего прочитанного сообщения',
	`parent_rel` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'объект с информацией о родительской сущности треда',
	PRIMARY KEY (`user_id`,`thread_map`),
	INDEX `get_thread_menu` (`user_id` ASC, `is_hidden` ASC, `updated_at` DESC, `created_at` ASC) COMMENT 'индекс для выборки тредов пользователя',
	INDEX `get_total_unread` (`user_id` ASC,`is_hidden` ASC,`unread_count` ASC) COMMENT 'индекс для того чтобы считать sum(unread_count) у пользователей',
        INDEX `get_favorite` (`user_id` ASC, `is_hidden` ASC, `is_favorite` ASC, `updated_at` ASC) COMMENT 'индекс для получения избранных'
	) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='таблица, в которой хранятся “все комментарии” пользователя';