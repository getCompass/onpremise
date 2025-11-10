use company_conversation;

ALTER TABLE `user_inbox` ADD COLUMN `single_conversation_unread_count` INT(11) NOT NULL DEFAULT 0 COMMENT 'количество непрочитанных личных чатов у пользователя' AFTER `conversation_unread_count`;