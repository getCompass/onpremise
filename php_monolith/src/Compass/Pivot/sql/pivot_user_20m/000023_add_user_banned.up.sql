CREATE TABLE IF NOT EXISTS `pivot_user_20m`.`user_banned` (
	`user_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID пользователя',
	`comment` VARCHAR(500) NOT NULL DEFAULT 0 COMMENT 'Комментарий',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	PRIMARY KEY (`user_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список заблокированных пользователей';
