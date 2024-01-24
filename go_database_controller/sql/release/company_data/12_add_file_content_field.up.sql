use company_data;

ALTER TABLE `file_list` ADD COLUMN `content` MEDIUMTEXT NOT NULL COMMENT 'содержимое файла для индексации' AFTER `extra`;