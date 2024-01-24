USE `file_node`;

ALTER TABLE `file` ADD COLUMN `is_cdn` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'использует ли загруженный файл cdn' AFTER `is_deleted`;
