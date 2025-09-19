-- -----------------------------------------------------
-- region file_node
-- -----------------------------------------------------

USE `file_node`;

CREATE TABLE IF NOT EXISTS `file_node`.`dlp_check_queue` (
    `queue_id` INT(11) NOT NULL AUTO_INCREMENT,
    `file_type` TINYINT(4) NOT NULL DEFAULT '0' COMMENT 'тип файла',
    `error_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'количество обработок записи кроном',
    `need_work` INT(11) NOT NULL DEFAULT '0' COMMENT 'время работы для крона',
    `file_key` VARCHAR(255) NOT NULL COMMENT 'map файла',
    `part_path` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'часть пути до файла',
    `extra` JSON NOT NULL COMMENT 'дополнительные данные для постобработки',
    PRIMARY KEY (`queue_id`),
    INDEX `need_work` (`need_work` ASC),
    INDEX `file_key` (`file_key` ASC),
    INDEX `file_type` (`file_type` ASC))
    ENGINE = InnoDB
    DEFAULT CHARACTER SET = utf8
    COMMENT = 'очередь на проверку файлов в DLP';

ALTER TABLE `file` ADD COLUMN `status` INT NOT NULL DEFAULT '1' COMMENT 'статус загрузки файла' AFTER `is_cdn`;

