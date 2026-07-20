use `ldap_data`;

CREATE TABLE IF NOT EXISTS `ldap_data`.`ldap_totp_user_rel` (
	`uid` VARCHAR(255) NOT NULL COMMENT 'uid пользователя в LDAP',
	`crypted_totp_secret` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'зашифрованный base32-секрет для TOTP',
	`created_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'когда привязали TOTP',
	`updated_at` INT UNSIGNED NOT NULL DEFAULT 0 COMMENT 'когда обновили запись',
	PRIMARY KEY (`uid`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'привязка TOTP-секрета к LDAP-пользователю';
