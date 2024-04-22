<?php

namespace Compass\Premise;

/**
 * абстрактный класс-интерфейс для работы с премайзом
 */
abstract class Gateway_Premise_Default {

	/**
	 * Взызвать premise метод
	 *
	 * @param string $url
	 * @param string $method
	 * @param array  $params
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	protected static function _doCall(string $url, string $method, array $params):array {

		// переводим в json параметры
		$json_params = toJson($params);

		$secret_key_config = Domain_Config_Entity_Main::get(Domain_Config_Entity_Main::SECRET_KEY);

		// если конфиг пустой - значит нет ключа у сервера
		$secret_key = $secret_key_config->value["secret_key"] ?? "";

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_KEY, $secret_key, $json_params
		);

		return Type_Premise_Main::doCall($url, $method, $json_params, $signature, sha1(PIVOT_DOMAIN), SERVER_UID);
	}
}
