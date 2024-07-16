<?php

namespace Compass\Jitsi;

/**
 * дефолтный класс для работы с сокетами
 */
class Gateway_Socket_Default {

	/**
	 * совершаем socket-запрос
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _doCall(
		string $url,
		string $method,
		array  $params,
		string $socket_key,
	):array {

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(
			Type_Socket_Auth_Handler::AUTH_TYPE_KEY,
			$socket_key,
			$json_params,
		);

		return Type_Socket_Main::doCall($url, $method, $json_params, $signature);
	}
}