ALTER TABLE `user_announcement_0` ADD INDEX `user_announcement_is_read` (`user_id`, `is_read`);
ALTER TABLE `user_announcement_0` ADD INDEX `user_announcement_need_resend` (`resend_attempted_at`, `next_resend_at`);
