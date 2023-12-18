<?php

namespace Compass\Conversation;

/**
 * Класс для работы с блоками сообщений (основной)
 */
class Domain_Conversation_Entity_Message_Block_Main {

	// удаляем запись с блоком из базы
	public static function delete(string $conversation_map, int $block_id):void {

		Gateway_Db_CompanyConversation_MessageBlock::delete($conversation_map, $block_id);
	}

	// проверяем, что блок активный/горячий
	public static function isActive(array $dynamic_row, int $block_id):bool {

		return true;
	}

	// проверяем существование блок
	public static function isExist(array $dynamic_row, int $block_id):bool {

		if ($block_id >= $dynamic_row["start_block_id"] && $block_id <= $dynamic_row["last_block_id"]) {
			return true;
		}
		return false;
	}

	// обновляем поле data в блоке сообщений
	public static function updateDataInMessageBlock(string $conversation_map, string $message_map, array $block_row, int $block_id, array $message):array {

		$block_row = Domain_Conversation_Entity_Message_Block_Message::set($message_map, $message, $block_row);

		// обновляем поле data
		Gateway_Db_CompanyConversation_MessageBlock::set($conversation_map, $block_id, [
			"data"       => $block_row["data"],
			"updated_at" => time(),
		]);

		return $message;
	}
}