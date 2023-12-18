USE `file_node` ;

ALTER TABLE file CHANGE COLUMN file_key `file_key` VARCHAR(500) NOT NULL COMMENT 'key файла';
ALTER TABLE post_upload_queue CHANGE COLUMN file_key `file_key` VARCHAR(500) NOT NULL COMMENT 'key файла';
ALTER TABLE relocate_queue CHANGE COLUMN file_key `file_key` VARCHAR(500) NOT NULL COMMENT 'key файла';
ALTER TABLE file_delete_by_expire_queue CHANGE COLUMN file_key `file_key` VARCHAR(500) NOT NULL COMMENT 'key файла';
