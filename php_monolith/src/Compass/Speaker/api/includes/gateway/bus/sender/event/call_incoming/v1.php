<?php

namespace Compass\Speaker;

/**
 * класс описывающий событие action.call_incoming версии 1
 */
class Gateway_Bus_Sender_Event_CallIncoming_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.call_incoming";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"call"           => \Entity_Validator_Structure::TYPE_OBJECT,
		"node_list"      => \Entity_Validator_Structure::TYPE_ARRAY,
		"caller_user_id" => \Entity_Validator_Structure::TYPE_INT,
	];

	/**
	 * собираем объект ws события
	 *
	 * @return Struct_Sender_Event
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(array $formatted_call, array $node_list, int $caller_user_id):Struct_Sender_Event {

		return self::_buildEvent([
			"call"           => (object) $formatted_call,
			"node_list"      => (array) $node_list,
			"caller_user_id" => (int) $caller_user_id,
		]);
	}
}