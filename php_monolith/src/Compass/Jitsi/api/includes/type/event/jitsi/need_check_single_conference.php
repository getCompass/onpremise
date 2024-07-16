<?php

namespace Compass\Jitsi;

/**
 * Событие — нужно проверить сингл звонок в jitsi
 *
 * @event_category partner
 * @event_name     send_task
 */
class Type_Event_Jitsi_NeedCheckSingleConference {

	/** @var string тип события */
	public const EVENT_TYPE = "jitsi.need_check_single_conference";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему
	 *
	 * @throws \parseException
	 */
	public static function create(int $space_id, int $check_until):Struct_Event_Base {

		$event_data = Struct_Event_Jitsi_NeedCheckSingleConference::build($space_id, $check_until);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 */
	public static function parse(array $event):Struct_Event_Jitsi_NeedCheckSingleConference {

		return Struct_Event_Jitsi_NeedCheckSingleConference::build(...$event["event_data"]);
	}
}
