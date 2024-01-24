<?php

namespace Compass\Announcement;

/**
 * класс для отдачи команд на клиент
 */
class Type_Api_Command {

	// основной метод - развилка
	public static function work(string $command_name, array $extra):array {

		return match ($command_name) {
			"force_update"     => self::_formResponse("force_update", $extra),
			"need_call_start"  => self::_formResponse("need_call_start", []),
			"need_confirm_2fa" => self::_formResponse("need_confirm_2fa", $extra),
			"require_pin_code" => self::_formResponse("require_pin_code", []),
			default            => throw new \parseException("Unhandled command_name named [$command_name] in " . __METHOD__),
		};
	}

	// формирует ответ под frontend
	protected static function _formResponse(string $type, array $data):array {

		return [
			"type" => (string) $type,
			"data" => (object) $data,
		];
	}
}
