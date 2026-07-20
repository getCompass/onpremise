<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — асинхронная очистка диалогов из source скрипта
 *
 * @event_category conversation
 */
class Type_Event_Conversation_AsyncSourceClearConversations
{
	/** @var string тип события */
	public const EVENT_TYPE = "conversation.async_source_clear_conversations";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws ParseFatalException
	 */
	public static function create(array $conversation_map_list, int $clear_until): Struct_Event_Base
	{

		$event_data = Struct_Event_Conversation_AsyncSourceClearConversations::build($conversation_map_list, $clear_until);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws ParseFatalException
	 */
	public static function parse(array $event): Struct_Event_Conversation_AsyncSourceClearConversations
	{

		return Struct_Event_Conversation_AsyncSourceClearConversations::build(...$event["event_data"]);
	}
}
