<?php

namespace Compass\Company;

/**
 * Класс описывающий событие action.employee_card_link_data_changed версии 1
 */
class Gateway_Bus_Sender_Event_EmployeeCardLinkDataChanged_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.employee_card_link_data_changed";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"link_list"   => \Entity_Validator_Structure::TYPE_ARRAY,
		"entity_type" => \Entity_Validator_Structure::TYPE_INT,
		"entity_id"   => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $link_list, int $entity_type, int $achievement_id):Struct_Sender_Event {

		return self::_buildEvent([
			"link_list"   => (array) $link_list,
			"entity_type" => (int) $entity_type,
			"entity_id"   => (int) $achievement_id,
		]);
	}
}