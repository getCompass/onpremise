<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.invite_link_created.v2
 *
 * @legacy весьма глупое решение, но ничего лучше я не придумал, чтобы поддерживать старых клиентов
 * в идеале нужно клиентов перевести на событие Gateway_Bus_Sender_Event_InviteLinkCreated_V2
 */
class Gateway_Bus_Sender_Event_InviteLinkCreatedV2_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "event.invite_link_created.v2";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"invite_link" => \Entity_Validator_Structure::TYPE_OBJECT,
		"join_link"   => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $invite_link, array $join_link):Struct_Sender_Event {

		return self::_buildEvent([
			"invite_link" => (object) $invite_link,
			"join_link"   => (object) $join_link,
		]);
	}
}