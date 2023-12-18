<?php

declare(strict_types = 1);

namespace Compass\Thread;

/**
 * Базовая структура события «очищен диалог для пользователей».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Thread_OnClearConversationForUserList extends Struct_Default {

	public string $conversation_map;
	public array  $user_id_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws array parseException
	 */
	public static function build(string $conversation_map, array $user_id_list):static {

		return new static([
			"conversation_map" => $conversation_map,
			"user_id_list"     => $user_id_list,
		]);
	}
}
