/* @formatter:off */

use company_thread;

DROP INDEX `get_thread_menu` ON `user_thread_menu`;
DROP INDEX `get_total_unread` ON `user_thread_menu`;
DROP INDEX `get_favorite` ON `user_thread_menu`;

CREATE INDEX `get_thread_menu` ON `user_thread_menu` (`user_id`, `is_hidden`, `updated_at` DESC, `created_at`);
CREATE INDEX `get_total_unread` ON `user_thread_menu` (`user_id`, `is_hidden`, `unread_count`);
CREATE INDEX `get_favorite` ON `user_thread_menu` (`user_id`, `is_hidden`, `is_favorite`, `updated_at`);