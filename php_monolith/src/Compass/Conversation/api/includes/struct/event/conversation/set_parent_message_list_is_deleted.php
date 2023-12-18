<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_SetParentMessageListIsDeleted extends Struct_Default {

	public array $thread_map_list;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(array $thread_map_list):static {

		return new static([
			"thread_map_list" => $thread_map_list,
		]);
	}
}
