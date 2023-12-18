<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.member.menu.read_notifications версии 1
 */
class Gateway_Bus_Sender_Event_MemberMenuReadNotifications_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int Название метода */
	protected const _WS_EVENT = "event.member.menu.read_notifications";

	/** @var int Версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array Структура ws события */
	protected const _WS_DATA = [
		"type_list" => \Entity_Validator_Structure::TYPE_ARRAY,
	];

	/**
	 * Собираем объект ws события
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $type_list):Struct_Sender_Event {

		return self::_buildEvent([
			"type_list" => (array) $type_list,
		]);
	}
}