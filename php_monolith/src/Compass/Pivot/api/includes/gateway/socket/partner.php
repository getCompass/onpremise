<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * класс-интерфейс для работы с модулем партнерского ядра
 */
class Gateway_Socket_Partner extends Gateway_Socket_Default {

	/**
	 * Проверяет, что указанная ссылка является ссылкой в компанию-группу поддержки.
	 */
	#[\JetBrains\PhpStorm\ArrayShape([0 => "string", 1 => "int"])]
	public static function getSupportCompanyLink():array {

		if (isTestServer() || ServerProvider::isOnPremise()) {
			return ["", 0];
		}

		[$status, $response] = self::_call("pivot.getSupportCompanyLink", []);

		if ($status !== "ok") {
			throw new ReturnFatalException("wrong response");
		}

		return [$response["join_link"], $response["valid_till"]];
	}

	/**
	 * делаем вызов
	 *
	 * @param string $method
	 * @param array  $params
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params):array {

		if (isTestServer() || ServerProvider::isOnPremise()) {
			return ["", []];
		}

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url       = self::_getUrl();
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, SOCKET_KEY_PIVOT, $json_params);

		// совершаем запрос
		return Type_Socket_Main::doCall($url, $method, $json_params, $signature);
	}

	/**
	 * получаем url
	 *
	 */
	protected static function _getUrl():string {

		$socket_module_config = getConfig("SOCKET_MODULE");
		return sprintf("%s://%s%s", PARTNER_PROTOCOL, PARTNER_DOMAIN, $socket_module_config["partner"]["socket_path"]);
	}
}
