use company_conversation;

ALTER TABLE `conversation_dynamic` ADD COLUMN `messages_updated_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'версия обновления сообщений в диалоге' AFTER `threads_updated_at`;
ALTER TABLE `conversation_dynamic` ADD COLUMN `reactions_updated_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'версия обновления реакций в диалоге' AFTER `messages_updated_version`;
ALTER TABLE `conversation_dynamic` ADD COLUMN `threads_updated_version` INT(11) NOT NULL DEFAULT 0 COMMENT 'версия обновления тредов в диалоге' AFTER `reactions_updated_version`;