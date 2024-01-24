USE `pivot_user_20m`;

CREATE TABLE IF NOT EXISTS `denied_user_free_premium` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'идентификатор пользователя',
	`created_at` INT(11) NOT NULL COMMENT 'дата создания записи',
	`reason_type` INT(11) NOT NULL COMMENT 'причина блокировки',
	PRIMARY KEY (`user_id`)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8 COMMENT 'таблица-список пользователей, которым нельзя активировать бесплатный премиум';
