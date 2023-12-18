use `company_system`;

CREATE TABLE IF NOT EXISTS `member_activity_list` (
	`user_id` BIGINT(20) NOT NULL COMMENT 'id пользователя',
	`day_start_at` INT(11) NOT NULL COMMENT 'timestamp начала дня, когда пользователь был активен',
	PRIMARY KEY (`user_id`,`day_start_at`),
	INDEX `day_start_at` (`day_start_at` ASC))
        ENGINE = InnoDB DEFAULT CHARACTER SET = utf8mb4 COMMENT='таблица для хранения активных пользователей в рамках компании';