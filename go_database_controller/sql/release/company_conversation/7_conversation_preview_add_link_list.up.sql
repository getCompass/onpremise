use company_conversation;

ALTER TABLE `conversation_preview` ADD COLUMN `link_list` JSON NOT NULL DEFAULT (JSON_ARRAY()) COMMENT 'список ссылок' AFTER `conversation_message_map`;