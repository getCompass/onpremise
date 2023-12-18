use company_conversation;

ALTER TABLE `conversation_preview` ADD INDEX `parent_message_map` (`parent_message_map` ASC);