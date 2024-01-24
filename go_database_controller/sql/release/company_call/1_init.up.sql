/* @formatter:off */

CREATE TABLE IF NOT EXISTS `call_meta` (
	`meta_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'уникальный идентификатор звонка в рамках таблицы',
	`creator_user_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, который инициализировал звонок',
	`is_finished` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0/1 - завершен ли звонок',
	`type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип звонка: 1 - single, 2 - group',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда звонок был создан',
	`started_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда звонок начался и завязался разговор',
	`finished_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда звонок закончился и разговор прекратился',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительные поля',
	`users` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'json массив с пользователями, участвующими в звонке',
	PRIMARY KEY (`meta_id`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'содержит основную информацию о существующих звонках в рамках дня';

CREATE TABLE IF NOT EXISTS `call_history` (
	`user_id` BIGINT(20) UNSIGNED NOT NULL COMMENT 'идентификатор пользователя',
	`call_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор звонка',
	`type` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'тип звонка',
	`creator_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор звонящего пользователя - создателя звонка',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда звонок был создан',
	`started_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда звонок перешел в статус разговора',
	`finished_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда звонок был завершен',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	PRIMARY KEY (`user_id`,`call_map`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица содержит записи с последним звонком пользователей - именно по данной таблице определяется занята ли линия конкретного пользователя';

CREATE TABLE IF NOT EXISTS `call_monitoring_dialing` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'user_id пользователя, у которого потерялась связь',
	`call_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор вызова',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда необходимо выполнить задачу',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Максимальное количество ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана задача',
	PRIMARY KEY (`user_id`,`call_map`),
	INDEX `need_work` (`need_work` ASC)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'очередь для проверки, что пользователь которому звонят - ответил на звонок';

CREATE TABLE IF NOT EXISTS `call_monitoring_establishing_connect` (
	`call_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор вызова',
	`user_id` BIGINT(20) NOT NULL COMMENT 'user_id пользователя, у которого потерялась связь',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда необходимо выполнить задачу',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'Максимальное количество ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана задача',
	PRIMARY KEY (`call_map`,`user_id`),
	INDEX `need_work` (`need_work` ASC)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'очередь для проверки, что пользователь установил соединение вовремя - в противном случае завершаем звонок';

CREATE TABLE IF NOT EXISTS `janus_connection_list` (
	`session_id` BIGINT(20) NOT NULL COMMENT 'уникальный идентификатор сессии для работы с соединением',
	`handle_id` BIGINT(20) NOT NULL COMMENT 'уникальный идентификатор handle для работы с плагином videoroom',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, для которого создано подключение',
	`publisher_user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'поле для subscribe подключения! идентификатор пользователя, которого слушает subscriber. Связь subscriber_user_id <-> publisher_user_id ',
	`connection_uuid` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'уникальный uuid идентификатор соединения пользователя с WebRTC Server',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус соединения (0 — установление подключения, 1 — подключен, 2 — переопдключение, 8 — соединение закончено)',
	`quality_state` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'состояние качества связи (0 - потеря соединения, 1 - среднее качества, 2 - отличное качество)',
	`is_publisher` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0/1 - является ли соединение publisher',
	`is_send_video` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0/1 - включена ли видеопередача у соединения (только для publisher)',
	`is_send_audio` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0/1 - включена ли аудиопередача у соединения (только для publisher)',
	`is_use_relay` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'флаг 0/1 - использует ли relay сервер это соединение',
	`publisher_upgrade_count` INT(11) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'Количество сколько раз пользователь переключил медиа (при получении SDP от Janus счетчик декрементится обратно)',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор ноды, используемый соединением для звонка',
	`audio_packet_loss` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество потерянных аудио пакетов с момента начала разговора',
	`video_packet_loss` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество потерянных видео пакетов с момента начала разговора',
	`audio_bad_quality_counter` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество случаев к ряду, когда качество аудио данных плохое',
	`video_bad_quality_counter` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество случаев к ряду, когда качество видео данных плохое',
	`audio_loss_counter` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество случаев к ряду, когда совсем не было передачи аудио пакетов',
	`video_loss_counter` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество случаев к ряду, когда совсем не было передачи видео пакетов',
	`last_ping_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда последний раз клиент пинговал сервер',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`participant_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор участника комнаты в Janus. Для publisher пользователя идентификатор ВСЕГДА равняется идентификатору пользователя в приложении',
	`room_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'уникальный идентификатор звонка в Janus',
	`call_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор звонка',
	PRIMARY KEY (`session_id`,`handle_id`),
	UNIQUE KEY `connection_uuid` (`connection_uuid`),
	INDEX `user_id_call_map_is_publisher` (`user_id` ASC,`call_map` ASC,`is_publisher` ASC),
	INDEX `call_map` (`call_map` ASC) COMMENT 'индекс для поиска соединений по call_map',
	INDEX `call_map_publisher_user_id` (`call_map` ASC,`participant_id` ASC) COMMENT 'индекс для поиска подписанных на юзера сабскрайберов',
	INDEX `call_map_is_publisher` (`call_map` ASC,`is_publisher` ASC) COMMENT 'индекс для поиска по call_map и is_publisher — чтобы выбирать все publisher-соединения звонка'
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'содержит соединения пользователя для publisher & subscriber';

CREATE TABLE IF NOT EXISTS `janus_room` (
	`room_id` BIGINT(20) NOT NULL COMMENT 'уникальный идентификатор звонка в janus',
	`call_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор звонка',
	`node_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор ноды Janus WebRTC Server, который был выбран для обслуживания звонка',
	`bitrate` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'уникальный идентификатор сессии для работы с Janus',
	`session_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'максимальный битрейт для комнаты',
	`handle_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'уникальный идентификатор plugin handle endpoint в Janus для плагина VideoRoom',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана комната в Janus',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	PRIMARY KEY (`room_id`),
	UNIQUE KEY `call_map` (`call_map`)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'содержит информацию о разговорной комнате звонка в Janus WebRTC Server';

CREATE TABLE IF NOT EXISTS `analytic_list` (
	`call_map` VARCHAR(255) NOT NULL COMMENT 'map идентификатор вызова',
	`user_id` BIGINT(20) NOT NULL COMMENT 'user_id пользователя, по соединениям которого собираем аналитику',
	`report_call_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор звонка для поиска аналитики',
	`reconnect_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во переподключений за это время',
	`middle_quality_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'кол-во плохого качества связи за это время',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
	`task_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор задачи',
	`last_row_id` BIGINT(20) UNSIGNED NOT NULL DEFAULT 0 COMMENT 'идентификатор последней записи с аналитикой',
	PRIMARY KEY (`call_map`,`user_id`),
	UNIQUE KEY `task_id` (`task_id`),
	INDEX `get_all` (`created_at` DESC) COMMENT 'индекс для получения списка записей',
	INDEX `get_all_by_user_id` (`user_id` ASC,`created_at` DESC) COMMENT 'индекс для получения списка записей с выборкой по user_id',
	INDEX `get_by_user_id_report_call_id` (`user_id` ASC,`report_call_id` ASC,`created_at` DESC) COMMENT 'индекс для поиска аналитики по user_id & report_call_id'
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица для роутинга запроса в архив или же горячую таблицу при получения аналитики звонка';

CREATE TABLE IF NOT EXISTS `analytic_queue` (
	`task_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор задачи',
	`call_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'map идентификатор вызова',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'user_id пользователя, по соединениям которого будем собирать аналитику',
	`need_work` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда необходимо выполнить задачу',
	`error_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество ошибок',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана задача',
	PRIMARY KEY (`task_id`),
	UNIQUE KEY `call_map` (`call_map`,`user_id`),
	INDEX `need_work` (`need_work` ASC) COMMENT 'индекс для крона producer'
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'очередь для сбора аналитики по соединениям пользователей звонков';

CREATE TABLE IF NOT EXISTS `report_connection_list` (
	`report_id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'уникальный идентификатор жалобы',
	`call_map` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'идентификатор звонка',
	`call_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор звонка, показываемый пользователю',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'id пользователя, который пожаловался на плохую связь',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус жалобы (0 - еще не рассматривали, 1 - в разработке оператором, 2 - отклонен, 3 - закрыт)',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка создания записи таблицы',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'временная метка обновления записи таблицы',
	`reason` VARCHAR(256) NOT NULL DEFAULT '' COMMENT 'текст жалобы на плохую связь',
	`extra` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'дополнительная информация о звонке',
	PRIMARY KEY (`report_id`),
	INDEX `get_by_call_map` (`call_map` ASC),
	INDEX `created_at` (`created_at` ASC),
	INDEX `status_user_id` (`status` ASC,`user_id` ASC)
	) ENGINE=InnoDB DEFAULT CHARACTER SET = utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT 'таблица содержит жалобы на плохую связь звонка';

CREATE TABLE IF NOT EXISTS `call_tester_queue` (
  `test_id` bigint NOT NULL AUTO_INCREMENT COMMENT 'идентификатор теста',
  `status` tinyint NOT NULL DEFAULT 0 COMMENT 'статус теста 0 - wip; 1 - success; 8 - failed',
  `need_work` int NOT NULL DEFAULT 0 COMMENT 'временная метка, когда нужно выполнить задачу',
  `stage` tinyint NOT NULL DEFAULT '1' COMMENT 'этап, на котором находится тест',
  `error_count` int NOT NULL DEFAULT 0 COMMENT 'количество ошибок выполнения задачи',
  `created_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была создана запись',
  `updated_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка, когда была обновлена запись',
  `finished_at` int NOT NULL DEFAULT 0 COMMENT 'временная метка, когда был закончен тест',
  `extra` json NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'вся необходимая информация для проведения тестового звонка',
  PRIMARY KEY (`test_id`),
  KEY `call_tester_queue` (`status`) COMMENT 'индекс для получения тасков кроном для их выполнения'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='таблица с тасками для проведения тестовых звонков';