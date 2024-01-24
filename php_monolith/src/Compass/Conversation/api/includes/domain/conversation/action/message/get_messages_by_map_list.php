<?php

namespace Compass\Conversation;

/**
 * Действие получения списка сообщение по указанному набору идентификаторов.
 */
class Domain_Conversation_Action_Message_GetMessagesByMapList {

	/**
	 * Возвращает список всех совпадений типа «Сообщение диалога».
	 */
	public static function run(array $message_map_list):array {

		// загружаем все необходимые блоки
		// и выбираем из них подходящие сообщения
		return static::_filterSpecifiedMessages(static::_loadMessageBlocks($message_map_list), $message_map_list);
	}

	/**
	 * Загружаем все блоки, которые содержат переданные сообщения.
	 * Не обращаем внимания на то, принадлежит ли они одному диалогу или нет.
	 */
	protected static function _loadMessageBlocks(array $message_map_list):array {

		$block_list_by_conversation_map = [];

		foreach ($message_map_list as $message_map) {

			// получаем map диалога и идентификатор блока сообщения
			$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
			$block_id         = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);

			$block_list_by_conversation_map[$conversation_map][] = $block_id;
		}

		if (count($block_list_by_conversation_map) === 0) {
			return [];
		}

		foreach ($block_list_by_conversation_map as $index => $block_id_list) {
			$block_list_by_conversation_map[$index] = array_values(array_unique($block_id_list));
		}

		return Gateway_Db_CompanyConversation_MessageBlock::getSpecifiedList($block_list_by_conversation_map);
	}

	/**
	 * Выполняет поиск запрошенных сообщений в списке блоков.
	 */
	protected static function _filterSpecifiedMessages(array $message_block_list, array $message_map_list):array {

		$flipped_key_list = array_flip($message_map_list);
		$output           = [];

		// пробегаем по всем блокам
		foreach ($message_block_list as $message_block) {

			// в каждом блоке ищем сообщения из указанного списка
			foreach (Domain_Conversation_Entity_Message_Block_Message::iterate($message_block) as $message) {

				if (!isset($flipped_key_list[$message["message_map"]])) {
					continue;
				}

				$output[] = $message;
			}
		}

		return $output;
	}
}