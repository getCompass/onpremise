<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о регистрации пользователя, которое будет отправлено в партнерку
 */
class Domain_Partner_Entity_Event_UserRegistered extends Domain_Partner_Entity_Event_Abstract {

	protected const _PARTNER_EVENT_TYPE = "user_registered";

	protected const _PARAMS_CURRENT_VERSION        = 1;
	protected const _PARAMS_SCHEMA_LIST_BY_VERSION = [

		1 => [
			"user_id"       => 0, // идентификатор пользователя создателя
			"source_type"   => "", // откуда зарегался
			"source_extra"  => [], // extra данные откуда зарегался
			"registered_at" => 0, // идентификатор пространства
		],
	];

	/**
	 * Создаем событие
	 *
	 * @param int    $user_id
	 * @param string $source_type
	 * @param array  $source_extra
	 * @param int    $registered_at
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $user_id, string $source_type, array $source_extra, int $registered_at):void {

		$params                  = self::_initParams();
		$params["user_id"]       = $user_id;
		$params["source_type"]   = $source_type;
		$params["source_extra"]  = $source_extra;
		$params["registered_at"] = $registered_at;
		$event_data              = self::_initEventData($params);

		// отправляем в партнерское ядро
		self::_sendToPartner($event_data);
	}

}