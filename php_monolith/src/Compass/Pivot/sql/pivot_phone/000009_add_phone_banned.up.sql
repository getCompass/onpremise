CREATE TABLE IF NOT EXISTS `pivot_phone`.`phone_banned` (
	`phone_number_hash` VARCHAR(40) NOT NULL COMMENT 'Хэш номера телефона',
	`comment` VARCHAR(500) NOT NULL DEFAULT 0 COMMENT 'Комментарий',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Время создания записи',
	PRIMARY KEY (`phone_number_hash`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT = 'Список заблокированных номеров телефонов';
