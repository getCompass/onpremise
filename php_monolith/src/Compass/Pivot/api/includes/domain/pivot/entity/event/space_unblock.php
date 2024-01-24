<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о разблокировке пространства
 */
class Domain_Pivot_Entity_Event_SpaceUnblock extends Domain_Pivot_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "space_tariff.space_unblock";

	protected const _PARAMS_CURRENT_VERSION        = 1;
	protected const _PARAMS_SCHEMA_LIST_BY_VERSION = [

		1 => [
			"space_id"    => 0,
			"check_until" => 0,
		],

	];

	/**
	 * создаем событие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $space_id, int $check_until):void {

		$event_data                = self::_initEventData();
		$event_data["space_id"]    = $space_id;
		$event_data["check_until"] = $check_until;

		// отправляем
		self::_send($event_data);
	}

}