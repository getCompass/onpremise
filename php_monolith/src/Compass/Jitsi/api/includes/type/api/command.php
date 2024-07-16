<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для отдачи команд на клиент
 */
class Type_Api_Command {

	// основной метод - развилка
	public static function work(string $command_name, array $extra):array {

		switch ($command_name) {

			case "need_call_start":
				return self::_formResponse("need_call_start", ["start_url" => PUBLIC_ENTRYPOINT_START]);

			default:

				throw new ParseFatalException("Unhandled command_name named [$command_name] in " . __METHOD__);
		}
	}

	// формирует ответ под frontend
	protected static function _formResponse(string $type, array $data):array {

		return [
			"type" => (string) $type,
			"data" => (object) $data,
		];
	}
}