<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.invite_link_deleted версии 1
 */
class Gateway_Bus_Sender_Event_InviteLinkDeleted_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.invite_link_deleted";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"invite_link_uniq" => \Entity_Validator_Structure::TYPE_STRING,
		"join_link_uniq"   => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(string $link_uniq):Struct_Sender_Event {

		return self::_buildEvent([
			"invite_link_uniq" => (string) $link_uniq,
			"join_link_uniq"   => (string) $link_uniq,
		]);
	}
}