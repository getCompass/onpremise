-- -----------------------------------------------------
-- Schema partner_invite_link
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `partner_invite_link` DEFAULT CHARACTER SET utf8;
USE `partner_invite_link` ;

-- -----------------------------------------------------
-- Table `partner_invite_link`.`invite_code_list_mirror`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `partner_invite_link`.`invite_code_list_mirror` (
	`invite_code` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Уникальная часть ссылки',
	`partner_id` BIGINT NOT NULL DEFAULT 0 COMMENT 'ID партнера',
	`created_at` INT NOT NULL DEFAULT 0 COMMENT 'Временная метка создания записи',
	PRIMARY KEY (`invite_code`))
	ENGINE = InnoDB
	DEFAULT CHARACTER SET = utf8mb4
	COLLATE = utf8mb4_unicode_ci
	COMMENT 'Таблица для зеркального хранения инвайт кодов с пивота';