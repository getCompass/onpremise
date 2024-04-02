use `sso_data`;

CREATE TABLE IF NOT EXISTS `sso_data`.`sso_auth_list` (
	`sso_auth_token` VARCHAR(36) NOT NULL COMMENT 'UUID попытки аутентификации',
	`signature` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'Случайная UUID строка – подпись, которую клиент передает вместе с sso_auth_token к каждому api-запросу процесса аутентификации. Необходима для защиты, поскольку sso_auth_token содержится в GET-параметрах ссылки для аутентификации в SSO и его могут перехватить (например в access.log веб-сервера)',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Статус попытки',
	`expires_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда попытка протухает',
	`completed_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда успешно завершилась попытка',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	`link` VARCHAR(2048) NOT NULL DEFAULT '' COMMENT 'Ссылка по которой был отправлен пользователь для прохождения аутентификации',
	`ua_hash` VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'SHA1 хэш сумма от user agent пользователя',
	`ip_address` VARCHAR(45) NOT NULL DEFAULT '' COMMENT 'IP адрес пользователя',
	PRIMARY KEY (`sso_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения всех попыток аутентификации через SSO';

CREATE TABLE IF NOT EXISTS `sso_data`.`sso_account_oidc_token_list` (
	`row_id` INT NOT NULL AUTO_INCREMENT COMMENT 'AI ID записи',
	`sub_hash` VARCHAR(40) NOT NULL DEFAULT '' COMMENT 'SHA1 хэш сумма (без использования соли) от sub (полученного в ID токене от SSO) – уникальный идентификатор, который используется для представления пользователя в системе, выдавшей токен',
	`sso_auth_token` VARCHAR(36) NOT NULL DEFAULT '' COMMENT 'UUID попытки аутентификации в результате которой был получен токен (нужно для неявной связи записей этой таблицы с записями таблицы auth_list)',
	`expires_at` INT NOT NULL DEFAULT '0' COMMENT 'Когда протухнет токен. Значение обновляется, если токен протух и был обновлен (refresh token)',
	`last_refresh_at` INT NOT NULL DEFAULT '0' COMMENT 'Когда в последний раз обновляли access_token',
	`created_at` INT NOT NULL DEFAULT '0' COMMENT 'Когда создали запись',
	`updated_at` INT NOT NULL DEFAULT '0' COMMENT 'Когда обновили запись',
	`data` MEDIUMTEXT NOT NULL COMMENT 'JSON структура полученная в результате успешной аутентификации или обновления токена',
	PRIMARY KEY (`row_id`),
	INDEX `sub_hash.row_id` (`sub_hash` ASC, `row_id` DESC) COMMENT 'Индекс для выборки токенов по аккаунту',
        INDEX `sso_auth_token` (`sso_auth_token`) COMMENT 'Индекс для выборки токенов по токену')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица, в которой будем хранить список токенов учетной записи, полученных в результате аутентификации через SSO';

CREATE TABLE IF NOT EXISTS `sso_data`.`sso_account_user_rel` (
	`sub_hash` VARCHAR(40) NOT NULL COMMENT 'SHA1 хэш сумма (без использования соли) от sub (полученного в ID токене от SSO) – уникальный идентификатор, который используется для представления пользователя в системе, выдавшей токен',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'ID пользователя Compass. По этому полю строится уникальный индекс user_id_UNIQUE',
	`sub_plain` VARCHAR(1024) NOT NULL COMMENT 'Параметр sub в чистом виде',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	PRIMARY KEY (`sub_hash`),
        UNIQUE INDEX `user_id_UNIQUE` (`user_id`) COMMENT 'Один пользователь Compass может иметь одну учетную запись SSO')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения отношения «Учетная запись SSO» ↔ «ID пользователя Compass»';
