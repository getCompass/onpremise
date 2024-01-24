<?php

namespace Compass\Conversation;

/**
 * Класс для флиьтрации входных данных
 */
class Domain_Conversation_Entity_Sanitizer {

	protected const _MAX_CONVERSATION_GET_COUNT = 100; // максимальное количество диалогов которое можно получить за раз

	/**
	 * фильтруем ключи компаний
	 */
	public static function sanitizeConversationMapList(array $conversation_map_list):array {

		$conversation_map_list = array_unique($conversation_map_list);

		if (count($conversation_map_list) < 1 || count($conversation_map_list) > self::_MAX_CONVERSATION_GET_COUNT) {
			throw new cs_IncorrectConversationMapList();
		}
		return $conversation_map_list;
	}
}