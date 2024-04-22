<?php

namespace Compass\Company;

/**
 * класс для формирования события покидания участником команды, которое будет отправлено в premise-модуль
 */
class Domain_Premise_Entity_Event_SpaceLeftMember extends Domain_Premise_Entity_Event_Abstract {

	protected const _EVENT_TYPE = "premise.space_left_member";

	/**
	 * Создаём событие
	 *
	 * @throws \BaseFrame\Exception\Gateway\BusFatalException
	 */
	public static function create(int $user_id):void {

		$params = [
			"user_id"  => $user_id,
			"space_id" => \CompassApp\System\Company::getCompanyId(),
		];

		self::_sendToPremise($params);
	}
}