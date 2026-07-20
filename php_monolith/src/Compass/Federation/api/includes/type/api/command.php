<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для отдачи команд на клиент
 */
class Type_Api_Command
{
	// основной метод - развилка
	public static function work(string $command_name, array $extra): array
	{

		switch ($command_name) {

			case "need_confirm_ldap_mail":
				return self::_formResponse("need_confirm_ldap_mail", $extra);
			case "need_setup_totp":
				return self::_formResponse("need_setup_totp", $extra);
			case "need_totp_code":
				return self::_formResponse("need_totp_code", $extra);
			default:

				throw new ParseFatalException("Unhandled command_name named [$command_name] in " . __METHOD__);
		}
	}

	// формирует ответ под frontend
	protected static function _formResponse(string $type, array $data): array
	{

		return [
			"type" => (string) $type,
			"data" => (object) $data,
		];
	}
}
