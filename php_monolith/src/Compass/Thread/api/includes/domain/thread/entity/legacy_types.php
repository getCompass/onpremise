<?php

namespace Compass\Thread;

use CompassApp\Pack\Message\Conversation;

/**
 * класс для фильтрации устаревших тредов / тредов из устаревших типов диалогов
 */
class Domain_Thread_Entity_LegacyTypes {

	/**
	 * Фильтруем массив с сущностями тред меню, исключая треды из диалога "Наймы и увольнения"
	 *
	 * @return array
	 */
	public static function filterThreadMenuFromJoinLegacy(array $thread_menu_list):array {

		// получаем conversation_map диалога "Наймы и увольнения"
		$hiring_conversation_map = Gateway_Socket_Conversation::getHiringConversationMap();

		// в ответе вернем список тредов из чата "Наймы и увольнения"
		$from_hiring_thread_menu_map_list = [];

		foreach ($thread_menu_list as $index => $thread_menu) {

			$parent_type = Type_Thread_ParentRel::getType($thread_menu["parent_rel"]);

			// если тред не имеет отношение к диалогу и заявкам на увольнение/принятие
			// то его не трогаем
			if (!in_array($parent_type, [PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE, PARENT_ENTITY_TYPE_HIRING_REQUEST, PARENT_ENTITY_TYPE_DISMISSAL_REQUEST])) {
				continue;
			}

			// если это тред к сообщению диалога
			if ($parent_type === PARENT_ENTITY_TYPE_CONVERSATION_MESSAGE) {

				// проверяем что тред к диалогу "Наймы и увольнения"
				$thread_conversation_message_map = Type_Thread_ParentRel::getMap($thread_menu["parent_rel"]);
				$thread_conversation_map         = Conversation::getConversationMap($thread_conversation_message_map);

				// если не совпали – то порядок
				if ($thread_conversation_map !== $hiring_conversation_map) {
					continue;
				}
			}

			// во всех остальных случаях сносим тред из ответа, а thread_map записываем чтобы вернуть из функции
			unset($thread_menu_list[$index]);
			$from_hiring_thread_menu_map_list[] = $thread_menu["thread_map"];
		}

		return [$thread_menu_list, $from_hiring_thread_menu_map_list];
	}
}
