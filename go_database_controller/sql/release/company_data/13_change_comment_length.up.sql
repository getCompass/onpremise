use company_data;

ALTER TABLE `member_list` CHANGE COLUMN `comment` `comment` VARCHAR(500) NOT NULL DEFAULT '' COMMENT 'комментарий к пользователю';