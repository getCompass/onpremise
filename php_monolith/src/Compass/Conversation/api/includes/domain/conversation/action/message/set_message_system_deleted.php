<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * помечаем сообщение удаленно системой
 */
class Domain_Conversation_Action_Message_SetMessageSystemDeleted {

	public static function do(string $message_map, array $meta_row):void {

		$block_id = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

		// получаем запись из dynamic и обновляем блок в горячей таблице
		$dynamic_row = Domain_Conversation_Entity_Dynamic::get($meta_row["conversation_map"]);

		if (Domain_Conversation_Entity_Message_Block_Main::isActive($dynamic_row, $block_id)) {
			$message = self::_doActive($meta_row["conversation_map"], $message_map, $block_id);
		} else {
			throw new ParseFatalException("Unsupported function");
		}

		// измененное сообщение - последнее в диалоге? то отправляем задачу на актуализацию left_menu в phphooker
		Type_Phphooker_Main::updateLastMessageOnMessageUpdateForSystemDelete($meta_row["conversation_map"], $message, $meta_row["users"]);

		// выполняем действия после пометки сообщения удаленным
		self::_after($meta_row["conversation_map"], $message_map, $message);
	}

	// активное
	protected static function _doActive(string $conversation_map, string $message_map, int $block_id):array {

		Gateway_Db_CompanyConversation_MessageBlock::beginTransaction();

		// получаем блок на обновление, получаем сообщение
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getForUpdate($conversation_map, $block_id);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		// помечаем сообщение удаленным
		$deleted_message = Type_Conversation_Message_Main::getHandler($message)::setSystemDeleted($message);

		$update_message_block = Domain_Conversation_Entity_Message_Block_Main::updateDataInMessageBlock($conversation_map, $message_map, $block_row, $block_id, $deleted_message);
		Gateway_Db_CompanyConversation_MessageBlock::commitTransaction();

		return $update_message_block;
	}

	// выполняем действия после пометки сообщения удаленным
	protected static function _after(string $conversation_map, string $message_map, array $message):void {

		// если в сообщении есть файл уменьшаем общее количество файлов в диалоге
		Type_Conversation_Message_Block::onDeleteMessageWithFile($conversation_map, $message);

		// проверяем, быть может уже прикреплен тред
		$thread_rel_row = Type_Conversation_ThreadRel::getThreadRelByMessageMap($conversation_map, $message_map);
		if (isset($thread_rel_row[$message_map])) {

			// отправляем задачу в phphooker, чтобы поставить треду is_deleted = 1
			Type_Phphooker_Main::setParentMessageIsDeletedIfThreadExist($thread_rel_row[$message_map]["thread_map"]);
		}
	}
}