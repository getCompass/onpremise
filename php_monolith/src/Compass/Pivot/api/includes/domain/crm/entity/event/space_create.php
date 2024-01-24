<?php

namespace Compass\Pivot;

/**
 * класс для формирования события о создании пространства пользователем
 */
class Domain_Crm_Entity_Event_SpaceCreate extends Domain_Crm_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "crm.space_create";

	/**
	 * Создаем событие
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 */
	public static function create(int $space_id):void {

		$params = [
			"space_id" => $space_id,
		];

		// отправляем в crm
		static::_sendToCrm($params);
	}

}