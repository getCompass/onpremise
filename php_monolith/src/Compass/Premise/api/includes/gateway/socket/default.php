<?php

namespace Compass\Premise;

/**
 * абстрактный класс-интерфейс для работы с сокетами
 */
abstract class Gateway_Socket_Default {

	// получаем подпись из массива параметров
	protected static function _doCall(string $url, string $method, array $params, int $company_id = 0, int $user_id = 0):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params
		);

		return Type_Socket_Main::doCall($url, $method, $json_params, $signature, $company_id, $user_id);
	}
}
