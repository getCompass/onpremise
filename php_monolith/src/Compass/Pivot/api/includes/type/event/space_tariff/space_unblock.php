<?php

namespace Compass\Pivot;

/**
 * Событие — разблокировано пространство
 *
 * @event_category partner
 * @event_name     send_task
 */
class Type_Event_SpaceTariff_SpaceUnblock {

	/** @var string тип события */
	public const EVENT_TYPE = "space_tariff.space_unblock";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему
	 *
	 * @throws \parseException
	 */
	public static function create(int $space_id, int $check_until):Struct_Event_Base {

		$event_data = Struct_Event_SpaceTariff_SpaceUnblock::build($space_id, $check_until);
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных
	 *
	 */
	public static function parse(array $event):Struct_Event_SpaceTariff_SpaceUnblock {

		return Struct_Event_SpaceTariff_SpaceUnblock::build(...$event["event_data"]);
	}
}
