<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие «Запрошено текстовое сообщение от системного бота».
 *
 * @event_category testing
 * @event_name     system_bot_text_message_requested
 */
class Type_Event_Testing_SystemBotTextMessageRequested {

	/** @var string тип события */
	public const EVENT_TYPE = "testing.system_bot_text_message_requested";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id, string $text):Struct_Event_Base {

		$event_data = Struct_Event_Testing_SystemBotTextMessageRequested::build($user_id, $text);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Testing_SystemBotTextMessageRequested {

		return Struct_Event_Testing_SystemBotTextMessageRequested::build(...$event["event_data"]);
	}
}
