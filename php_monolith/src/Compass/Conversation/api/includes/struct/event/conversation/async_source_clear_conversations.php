<?php

declare(strict_types=1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Структура события асинхронной очистки диалогов из source скрипта
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Event_Conversation_AsyncSourceClearConversations extends Struct_Default
{
	public array $conversation_map_list;

	public int $clear_until;

	/**
	 * Статический конструктор.
	 *
	 * @return Struct_Event_Conversation_AsyncSourceClearConversations
	 * @throws ParseFatalException
	 */
	public static function build(array $conversation_map_list, int $clear_until): static
	{

		return new static([
			"conversation_map_list" => $conversation_map_list,
			"clear_until"           => $clear_until,
		]);
	}
}
