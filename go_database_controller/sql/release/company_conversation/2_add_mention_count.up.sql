/* @formatter:off */

use company_conversation;

ALTER TABLE `user_left_menu` ADD COLUMN `mention_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'число упоминаний пользователя в чате левого меню' AFTER `unread_count`;
