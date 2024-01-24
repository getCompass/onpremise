use company_conversation;

ALTER TABLE `conversation_file` ADD INDEX `get_user_files` (`is_deleted`, `file_type`, `parent_type`, `conversation_map`, `user_id`, `row_id`, `conversation_message_created_at`);