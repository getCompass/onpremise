use company_conversation;

ALTER TABLE `message_thread_rel`  ADD INDEX `conversation_map_and_block_id` (`conversation_map`, `block_id`);