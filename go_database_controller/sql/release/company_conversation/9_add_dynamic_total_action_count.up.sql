use company_conversation;

ALTER TABLE `conversation_dynamic` ADD COLUMN `total_action_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'общее количество действий в диалоге' AFTER `total_message_count`;