<?php

declare(strict_types = 1);

namespace Compass\Conversation;

/**
 * Структура события обновление conversation_name группового диалога
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_SetDeletedThreadRepostRel extends Struct_Default {

	public string $thread_map;

	public string $message_map;

	/**
	 * Статический конструктор.
	 *
	 * @throws \parseException
	 * @ehrows \parseException
	 */
	public static function build(string $thread_map, string $message_map):static {

		return new static([
			"thread_map"  => $thread_map,
			"message_map" => $message_map,
		]);
	}
}
