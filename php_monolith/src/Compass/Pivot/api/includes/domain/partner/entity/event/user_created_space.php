<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о создании пространства пользователем, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserCreatedSpace extends Domain_Partner_Entity_Event_Abstract {

	protected const _PARTNER_EVENT_TYPE = "user_created_space";

	protected const _PARAMS_CURRENT_VERSION        = 1;
	protected const _PARAMS_SCHEMA_LIST_BY_VERSION = [

		1 => [
			"user_id"  => 0, // идентификатор пользователя создателя
			"space_id" => 0, // идентификатор пространства
		],
	];

	/**
	 * Создаем событие
	 *
	 * @param int $user_id
	 * @param int $space_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, int $space_id):void {

		$params             = self::_initParams();
		$params["user_id"]  = $user_id;
		$params["space_id"] = $space_id;
		$event_data         = self::_initEventData($params);

		// отправляем в партнерское ядро
		self::_sendToPartner($event_data);
	}

}