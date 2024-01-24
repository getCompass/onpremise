<?php

namespace Compass\Company;

/**
 * Класс описывающий событие event.member.menu.undo_notification версии 1
 */
class Gateway_Bus_Sender_Event_MemberMenuUndoNotification_V1 extends Gateway_Bus_Sender_Event_Abstract {

	/** @var int Название метода */
	protected const _WS_EVENT = "event.member.menu.undo_notification";

	/** @var int Версия метода */
	protected const _WS_EVENT_VERSION = 1;

	/** @var array Структура ws события */
	protected const _WS_DATA = [
		"action_user_id" => \Entity_Validator_Structure::TYPE_INT,
		"type"           => \Entity_Validator_Structure::TYPE_STRING,
		"data"           => \Entity_Validator_Structure::TYPE_OBJECT,
	];

	/**
	 * Собираем объект ws события
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function makeEvent(int $action_user_id, string $type, array $data):Struct_Sender_Event {

		return self::_buildEvent([
			"action_user_id" => (int) $action_user_id,
			"type"           => (string) $type,
			"data"           => (object) $data,
		]);
	}
}