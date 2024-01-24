<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс описывающий событие action.answer_debug_info версии 1
 */
class Gateway_Bus_Sender_Event_AnswerDebugInfo_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int название метода */
	protected const _WS_EVENT = "action.answer_debug_info";

	/** @var int версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array структура ws события */
	protected const _WS_DATA = [
		"conversation_key" => \Entity_Validator_Structure::TYPE_STRING,
		"text"             => \Entity_Validator_Structure::TYPE_STRING,
	];

	/**
	 * собираем объект ws события
	 *
	 * @param string $conversation_key
	 * @param string $text
	 *
	 * @return Struct_Sender_Event
	 * @throws ParseFatalException
	 */
	public static function makeEvent(string $conversation_key, string $text):Struct_Sender_Event {

		return self::_buildEvent([
			"conversation_key" => (string) $conversation_key,
			"text"             => (string) $text,
		]);
	}
}