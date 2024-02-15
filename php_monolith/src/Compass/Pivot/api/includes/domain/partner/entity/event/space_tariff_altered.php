<?php

namespace Compass\Pivot;

/**
 * класс для формирования события об изменении тарифного плана в пространстве
 */
class Domain_Partner_Entity_Event_SpaceTariffAltered extends Domain_Partner_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "partner.space_tariff_altered";

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

		// отправляем в партнерское ядро
		static::_sendToPartner($params);
	}

}