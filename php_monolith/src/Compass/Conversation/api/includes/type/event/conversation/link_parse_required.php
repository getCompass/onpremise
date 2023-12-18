<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Событие — требуется распарсить ссылку.
 *
 * @event_category conversation
 * @event_name     link_parse_required
 */
class Type_Event_Conversation_LinkParseRequired {

	/** @var string тип события */
	public const EVENT_TYPE = "conversation.link_parse_required";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @param string $message_map
	 * @param int    $user_id
	 * @param array  $link_list
	 * @param string $lang
	 * @param array  $user_list
	 * @param array  $entity_info
	 * @param bool   $need_full_preview
	 *
	 * @return Struct_Event_Base
	 * @throws ParseFatalException
	 * @throws \parseException
	 */
	public static function create(string $message_map, int $user_id, array $link_list, string $lang, array $user_list, array $entity_info, bool $need_full_preview):Struct_Event_Base {

		$event_data = Struct_Event_Conversation_LinkParseRequired::build($message_map, $user_id, $link_list, $lang, $user_list, $entity_info, $need_full_preview);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Conversation_LinkParseRequired {

		return Struct_Event_Conversation_LinkParseRequired::build(...$event["event_data"]);
	}
}
