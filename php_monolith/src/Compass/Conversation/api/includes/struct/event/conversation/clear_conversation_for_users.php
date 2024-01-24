<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Структура события очистки диалога у пользователей (conversation.clear_conversation_for_users)
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_ClearConversationForUsers extends Struct_Default {

	public string $conversation_map;

	public array $user_id_list;

	public int $messages_updated_version;

	/**
	 * Статический конструктор.
	 *
	 * @param string $conversation_map
	 * @param array  $user_id_list
	 * @param int    $messages_updated_version
	 *
	 * @return Struct_Event_Conversation_ClearConversationForUsers
	 * @throws ParseFatalException
	 */
	public static function build(string $conversation_map, array $user_id_list, int $messages_updated_version):static {

		return new static([
			"conversation_map"         => $conversation_map,
			"user_id_list"             => $user_id_list,
			"messages_updated_version" => $messages_updated_version,
		]);
	}
}
