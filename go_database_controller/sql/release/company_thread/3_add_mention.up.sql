/* @formatter:off */

use company_thread;

ALTER TABLE `user_thread_menu` ADD COLUMN `is_mentioned` TINYINT(1) NOT NULL DEFAULT '0' COMMENT 'флаг 0/1 упомянут ли пользователь в треде' AFTER `is_favorite`;
ALTER TABLE `user_thread_menu` ADD COLUMN `mention_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'число упоминаний пользователя в треде' AFTER `unread_count`;

DROP INDEX `get_thread_menu` ON `user_thread_menu`;
DROP INDEX `get_total_unread` ON `user_thread_menu`;
DROP INDEX `get_favorite` ON `user_thread_menu`;

CREATE INDEX `get_thread_menu` ON `user_thread_menu` (`user_id`, `is_hidden`, `is_mentioned` DESC, `updated_at` DESC, `created_at`);
CREATE INDEX `get_total_unread` ON `user_thread_menu` (`user_id`, `is_hidden`, `is_mentioned` DESC, `updated_at` DESC);
CREATE INDEX `get_favorite` ON `user_thread_menu` (`user_id`, `is_hidden`, `is_favorite`, `is_mentioned` DESC, `updated_at` DESC);

ALTER TABLE `user_inbox` ADD COLUMN `thread_mention_count` INT(11) NOT NULL DEFAULT '0' COMMENT 'число тредов, которых упомянут пользователь' AFTER `thread_unread_count`;