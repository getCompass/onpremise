use company_conversation;

ALTER TABLE `conversation_file` ADD INDEX `grom_user_files` (`conversation_map` ASC,`is_deleted` ASC, `file_type` ASC,`parent_type` ASC, `conversation_message_created_at` ASC, `row_id` DESC, `user_id` ASC);
ALTER TABLE `conversation_file` ADD INDEX `grom_viewer_user_files` (`conversation_map` ASC,`is_deleted` ASC, `file_type` ASC,`parent_type` ASC, `conversation_message_created_at` ASC, `created_at` DESC, `user_id` ASC);
ALTER TABLE `conversation_preview` ADD INDEX `grom_user_previews` (`conversation_map` ASC,`is_deleted` ASC,`parent_type` ASC, `parent_message_created_at` ASC, `conversation_message_created_at` DESC, `user_id` ASC);