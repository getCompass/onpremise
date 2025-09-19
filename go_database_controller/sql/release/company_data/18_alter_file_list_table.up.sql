use company_data;

ALTER TABLE `file_list` ADD COLUMN `status` INT NOT NULL DEFAULT '1' COMMENT 'статус загрузки файла' AFTER `is_cdn`;
