<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_SetParentMessageIsDeleted extends Struct_Default {

	public int $thread_map;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(string $thread_map):static {

		return new static([
			"thread_map" => $thread_map,
		]);
	}
}
