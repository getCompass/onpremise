<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о присоединении к пространству пользователя, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserJoinedSpace extends Domain_Partner_Entity_Event_Abstract {

	protected const _PARTNER_EVENT_TYPE = "user_joined_space";

	protected const _PARAMS_CURRENT_VERSION        = 1;
	protected const _PARAMS_SCHEMA_LIST_BY_VERSION = [

		1 => [
			"user_id"            => 0, // идентификатор пользователя создателя
			"space_id"           => 0, // идентификатор пространства
			"user_registered_at" => 0, // время регистрации пользователя
		],
	];

	/**
	 * Создаем событие
	 *
	 * @param int $user_id
	 * @param int $space_id
	 * @param int $user_registered_at
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, int $space_id, int $user_registered_at):void {

		$params                       = self::_initParams();
		$params["user_id"]            = $user_id;
		$params["space_id"]           = $space_id;
		$params["user_registered_at"] = $user_registered_at;
		$event_data                   = self::_initEventData($params);

		// отправляем в партнерское ядро
		self::_sendToPartner($event_data);
	}

}