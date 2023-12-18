use `pivot_data`;

CREATE TABLE IF NOT EXISTS `last_registered_user` (
	`user_id` BIGINT(20) NOT NULL AUTO_INCREMENT COMMENT 'идентификатор пользователя',
	`partner_id` BIGINT(20) NOT NULL COMMENT 'идентификатор партнера, пригласившего пользователя',
	`created_at` INT(11) NOT NULL COMMENT 'дата создания записи',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'дата обновления записи',
	`extra` JSON NOT NULL COMMENT 'экстра-данные для записи',
	PRIMARY KEY (`user_id`),
    INDEX `get_by_partner` (`partner_id` ASC, `created_at` DESC)
) ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COMMENT='таблица для хранения данных последний пользовательских регистраций';
