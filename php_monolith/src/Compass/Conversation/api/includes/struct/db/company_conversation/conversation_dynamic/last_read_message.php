<?php

namespace Compass\Conversation;

/**
 * Класс-структура для поля last_read_message в таблице company_conversation.conversation_dynamic
 */
class Struct_Db_CompanyConversation_ConversationDynamic_LastReadMessage {

	/**
	 * @param string $message_map
	 * @param int    $conversation_message_index
	 * @param array  $read_participants
	 */
	public function __construct(
		public string $message_map,
		public int    $conversation_message_index,
		public array  $read_participants,
	) {

	}

	/**
	 * Построить объект из массива
	 *
	 * @param array $array
	 *
	 * @return Struct_Db_CompanyConversation_ConversationDynamic_LastReadMessage
	 */
	public static function fromArray(array $array):self {

		return new self(
			$array["message_map"] ?? "",
			$array["conversation_message_index"] ?? 0,
			$array["read_participants"] ?? [],
		);
	}
}