<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о смене данных пользователя, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserUpdatedInfo extends Domain_Partner_Entity_Event_Abstract {

	protected const _PARTNER_EVENT_TYPE = "user_updated_info";

	protected const _PARAMS_CURRENT_VERSION        = 1;
	protected const _PARAMS_SCHEMA_LIST_BY_VERSION = [

		1 => [
			"user_id" => 0, // идентификатор пользователя
		],
	];

	/**
	 * Создаем событие
	 *
	 * @param int $user_id
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id):void {

		$params            = self::_initParams();
		$params["user_id"] = $user_id;
		$event_data        = self::_initEventData($params);

		// отправляем в партнерское ядро
		self::_sendToPartner($event_data);
	}

}