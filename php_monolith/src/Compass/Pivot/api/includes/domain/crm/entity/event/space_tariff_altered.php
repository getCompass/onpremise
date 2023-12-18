<?php

namespace Compass\Pivot;

/**
 * класс для формирования события об изменении тарифного плана в пространстве
 */
class Domain_Crm_Entity_Event_SpaceTariffAltered extends Domain_Crm_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "crm.space_tariff_altered";

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