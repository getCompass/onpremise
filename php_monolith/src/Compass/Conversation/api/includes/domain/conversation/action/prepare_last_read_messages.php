<?php

namespace Compass\Conversation;

/**
 * Подготовить просмотревших пользователей
 */
class Domain_Conversation_Action_PrepareLastReadMessages {

	/**
	 * Подготовить просмотревших пользователей
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic[] $dynamic_list
	 *
	 * @return Struct_Conversation_LastReadMessage[]
	 */
	public static function do(array $dynamic_list):array {

		$dynamic_list = self::_filterDynamicLastReadMessages($dynamic_list);

		// готовим список последних сообщений
		return array_map(function(Struct_Db_CompanyConversation_ConversationDynamic $dynamic) {

			return is_null($dynamic->last_read_message)
				? self::_makeEmptyLastMessage()
				: self::_makeLastMessage($dynamic->last_read_message);
		}, $dynamic_list);
	}

	/**
	 * Отфильтровать последние сообщения в dynamic записях
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic[] $dynamic_list
	 *
	 * @return array
	 */
	protected static function _filterDynamicLastReadMessages(array $dynamic_list):array {

		// если запрещено показывать статус прочитанности сообщений, убираем плашки везде
		if (!Domain_Company_Action_Config_GetShowMessageReadStatus::do()) {
			return self::_removeLastReadMessages($dynamic_list);
		}

		return $dynamic_list;
	}

	/**
	 * Удалить последние сообщения из всех dynamic записей
	 *
	 * @param array $dynamic_list
	 *
	 * @return array
	 */
	protected static function _removeLastReadMessages(array $dynamic_list):array {

		return array_map(
			function(Struct_Db_CompanyConversation_ConversationDynamic $dynamic) {

				$dynamic->last_read_message = null;
				return $dynamic;
			},
			$dynamic_list);
	}

	/**
	 * Вернуть пустое последнее сообщение
	 *
	 * @return Struct_Conversation_LastReadMessage
	 */
	protected static function _makeEmptyLastMessage():Struct_Conversation_LastReadMessage {

		return new Struct_Conversation_LastReadMessage(
			"",
			0,
			0,
			[]
		);
	}

	/**
	 * Подготовить последнее сообщение для чата
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic_LastReadMessage $dynamic_last_read_message
	 *
	 * @return Struct_Conversation_LastReadMessage
	 */
	protected static function _makeLastMessage(Struct_Db_CompanyConversation_ConversationDynamic_LastReadMessage $dynamic_last_read_message):Struct_Conversation_LastReadMessage {

		// сортируем по дате прочтения в порядке возрастания
		uasort($dynamic_last_read_message->read_participants, static function(int $a, int $b) {

			return $a <=> $b;
		});

		$first_read_participants_list = array_slice(array_keys($dynamic_last_read_message->read_participants), 0, 5);

		return new Struct_Conversation_LastReadMessage(
			$dynamic_last_read_message->message_map,
			$dynamic_last_read_message->conversation_message_index,
			count($dynamic_last_read_message->read_participants),
			$first_read_participants_list
		);
	}
}