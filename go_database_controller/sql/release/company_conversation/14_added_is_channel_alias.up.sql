/* @formatter:off */

use company_conversation;

ALTER TABLE `user_left_menu` ADD COLUMN `is_channel_alias` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'alias для опции канала' AFTER `is_have_notice`;
