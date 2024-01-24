-- -----------------------------------------------------
-- region file_node
-- -----------------------------------------------------

USE `file_node`;

CREATE INDEX `type` ON `post_upload_queue` (`file_type`);