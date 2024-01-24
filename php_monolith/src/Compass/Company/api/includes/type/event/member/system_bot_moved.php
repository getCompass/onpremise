<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Событие — изменился тип чата оповещения
 *
 * @event_category member
 * @event_name     system_bot_moved
 */
class Type_Event_Member_SystemBotMoved {

	/** @var string тип события */
	public const EVENT_TYPE = "member.system_bot_moved";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(int $user_id):Struct_Event_Base {

		$event_data = Struct_Event_Member_SystemBotMoved::build($user_id);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_Member_SystemBotMoved {

		return Struct_Event_Member_SystemBotMoved::build(...$event["event_data"]);
	}
}
