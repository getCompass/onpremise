use `ldap_data`;

CREATE TABLE IF NOT EXISTS `ldap_data`.`ldap_auth_list` (
	`ldap_auth_token` VARCHAR(36) NOT NULL COMMENT 'UUID попытки аутентификации',
	`status` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Статус попытки',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	`uid` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Уникальный ID учетной записи. Как правило он совпадает с username, но не всегда (зависит от провайдера LDAP и настроек)',
	`username` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Username попытки',
	`dn` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Уникальный идентификатор записи (объекта) в LDAP-дереве (например: uid=ivan_ivanov,cn=users,cn=accounts,dc=example,dc=com) учетной записи, в которую успешно авторизовались. Для неудачной попытки здесь будет пустота',
	`data` MEDIUMTEXT NOT NULL COMMENT 'Версионная JSON структура с информацией об учетной записи. Заполняется только в случае успешной валидации username:password в LDAP провайдере',
	PRIMARY KEY (`ldap_auth_token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения всех попыток аутентификации через LDAP';

CREATE TABLE IF NOT EXISTS `ldap_data`.`ldap_account_user_rel` (
	`uid` VARCHAR(255) NOT NULL COMMENT 'Уникальный ID учетной записи. Как правило он совпадает с username, но не всегда (зависит от провайдера LDAP и настроек)',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'ID пользователя Compass. По этому полю строится уникальный индекс user_id_UNIQUE',
	`status` INT(11) NOT NULL DEFAULT 0 COMMENT 'Статус связи',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда создали запись',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'Когда обновили запись',
	`username` VARCHAR(255) NOT NULL COMMENT 'Username учетной записи',
	`dn` VARCHAR(255) NOT NULL COMMENT 'Уникальный идентификатор записи (объекта) в LDAP-дереве (например: uid=ivan_ivanov,cn=users,cn=accounts,dc=example,dc=com)',
	PRIMARY KEY (`uid`),
	UNIQUE INDEX `user_id_UNIQUE` (`user_id`) COMMENT 'Один пользователь Compass может иметь одну учетную запись LDAP')
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'таблица для хранения отношения «Учетная запись LDAP» ↔ «ID пользователя Compass»';
