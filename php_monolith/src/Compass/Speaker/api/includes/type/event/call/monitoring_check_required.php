<?php

namespace Compass\Speaker;

/**
 * Событие — проверить мониторинг звонков
 *
 * @event_category call
 * @event_name     monitoring_check_required
 */
class Type_Event_Call_MonitoringCheckRequired {

	/** @var string тип события */
	public const EVENT_TYPE = "call.monitoring_check_required";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create():Struct_Event_Base {

		$event_data = Struct_Event_Call_MonitoringCheckRequired::build();
		return Type_Event_Base::create(self::EVENT_TYPE, $event_data);
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 * @noinspection PhpUnusedParameterInspection
	 */
	public static function parse(array $event):Struct_Event_Call_MonitoringCheckRequired {

		return Struct_Event_Call_MonitoringCheckRequired::build();
	}
}
