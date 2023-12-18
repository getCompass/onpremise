<?php

namespace Compass\Conversation;

/**
 * Action для получения map из ключа conversation_key
 */
class Domain_Conversation_Action_GetMapFromConversationKey {

	/**
	 * выполняем
	 *
	 * @throws \paramException
	 */
	public static function do(array $conversation_key_list):array {

		$conversation_map_list = [];
		foreach ($conversation_key_list as $v) {

			// получаем conversation_map
			$conversation_map = self::_tryGetConversationMap($v);

			// дополняем сообщения
			$conversation_map_list[] = $conversation_map;
		}

		return array_unique($conversation_map_list);
	}

	/**
	 * получаем conversation_map из conversation_key
	 *
	 * @throws \paramException
	 */
	protected static function _tryGetConversationMap(string $conversation_key):string {

		return \CompassApp\Pack\Conversation::tryDecrypt($conversation_key);
	}
}