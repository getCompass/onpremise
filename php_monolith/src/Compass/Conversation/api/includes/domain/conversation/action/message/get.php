<?php

namespace Compass\Conversation;

use CompassApp\Pack\Message\Conversation;

/**
 * Действие получения сообщения по его message_map
 */
class Domain_Conversation_Action_Message_Get {

	/**
	 * Получить сообщение из блока
	 *
	 * @param string $message_map
	 *
	 * @return array
	 * @throws \cs_UnpackHasFailed
	 */
	public static function do(string $message_map):array {

		// получаем id блока, откуда этот message_map
		$block_id         = Conversation::getBlockId($message_map);
		$conversation_map = Conversation::getConversationMap($message_map);

		$message_block = Gateway_Db_CompanyConversation_MessageBlock::getOne($conversation_map, $block_id);

		// достаем сообщение из блока сообщений
		return Domain_Conversation_Entity_Message_Block_Message::get($message_map, $message_block);
	}

}