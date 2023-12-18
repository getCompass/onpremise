use company_conversation;

CREATE INDEX `get_unread_menu_v2` ON `user_left_menu` (`user_id` ASC, `is_hidden` ASC, `is_have_notice` ASC, `is_mentioned` DESC, (FIELD(`type`, 1)) DESC, `updated_at` DESC);
