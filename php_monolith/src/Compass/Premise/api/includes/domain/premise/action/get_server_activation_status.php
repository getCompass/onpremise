<?php

namespace Compass\Premise;

/**
 * Узнать статус активации сервера
 */
class Domain_Premise_Action_GetServerActivationStatus {

	public const ACTIVATED_STATUS   = "activated"; // активирован
	public const UNACTIVATED_STATUS = "unactivated"; // неактивирован

	/**
	 * Выполняем
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do():array {


		$secret_key_config = Domain_Config_Entity_Main::get(Domain_Config_Entity_Main::SECRET_KEY);

		if ($secret_key_config->value === []) {
			return [self::UNACTIVATED_STATUS, self::_getUnactivatedData()];
		}

		if (!isset($secret_key_config->value["secret_key"]) || $secret_key_config->value["secret_key"] == "") {
			return [self::UNACTIVATED_STATUS, self::_getUnactivatedData()];
		}

		return [self::ACTIVATED_STATUS, []];
	}

	/**
	 * Получить данные для неактивированного сервера
	 *
	 * @return array
	 */
	protected static function _getUnactivatedData():array {

		return [
			"activation_message" => SERVER_ACTIVATION_MESSAGE,
		];
	}
}
