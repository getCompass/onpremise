USE `pivot_userbot`;

CREATE TABLE IF NOT EXISTS `userbot_list` (
	`userbot_id` CHAR(32) NOT NULL DEFAULT '' COMMENT 'идентификатор бота',
	`company_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'идентификатор компании, в которую добавили бота',
	`status` TINYINT(4) NOT NULL DEFAULT 0 COMMENT 'статус бота',
	`user_id` BIGINT(20) NOT NULL DEFAULT 0 COMMENT 'идентификатор пользователя, за которым закреплён бот',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`extra` JSON NOT NULL COMMENT 'доп. данные',
	PRIMARY KEY (`userbot_id`, `company_id`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'используется для хранения ботов и связи сущностей бота и пользователя';

CREATE TABLE IF NOT EXISTS `token_list` (
	`token` VARCHAR(23) NOT NULL DEFAULT '' COMMENT 'идентификатор бота',
	`userbot_id` CHAR(32) NOT NULL DEFAULT '' COMMENT 'идентификатор бота',
	`created_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись создана',
	`updated_at` INT(11) NOT NULL DEFAULT 0 COMMENT 'когда запись обновлена',
	`extra` JSON NOT NULL COMMENT 'доп. данные',
	PRIMARY KEY (`token`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8
	COMMENT 'используется для хранения токенов';